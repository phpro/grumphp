<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\Php\PhpParser as Parser;
use GrumPHP\Parser\Php\PhpParserError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\PhpParser;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PhpParserTest extends AbstractTaskTestCase
{
    /**
     * @var Parser|ObjectProphecy
     */
    private $parser;

    protected function provideTask(): TaskInterface
    {
        $this->parser = $this->prophesize(Parser::class);
        $this->parser->isInstalled()->willReturn(true);

        return new PhpParser($this->parser->reveal());
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'kind' => PhpParser::KIND_PHP7,
                'visitors' => [],
                'triggered_by' => ['php'],
                'ignore_patterns' => [],
            ]
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            $this->mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            $this->mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            $this->mockContext()
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        $prefix = "Some errors occured while parsing your PHP files:\n";

        yield 'invalid-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()->first())->willReturn(new ParseErrorsCollection([
                    $this->createParseError('hello.php'),
                    $this->createParseError('hello.php'),
                ]));
            },
            $prefix.(new ParseErrorsCollection([
                $this->createParseError('hello.php'),
                $this->createParseError('hello.php'),
            ]))
        ];
        yield 'invalid-files' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'world.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([
                    $this->createParseError('hello.php'),
                    $this->createParseError('hello.php'),
                ]));
                $this->parser->parse($context->getFiles()[1])->willReturn(new ParseErrorsCollection([
                    $this->createParseError('world.php'),
                ]));
            },
            $prefix.(new ParseErrorsCollection([
                $this->createParseError('hello.php'),
                $this->createParseError('hello.php'),
                $this->createParseError('world.php'),
            ]))
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'no-lint-errors' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([]));
            }
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            $this->mockContext(RunContext::class, ['test/file.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([]));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            $this->mockContext(RunContext::class, ['notaymlfile.txt']),
            function () {}
        ];
    }

    private function assumeParserConfig(array $options)
    {
        $this->parser->setParserOptions($options)->shouldBeCalled();
    }

    private function createParseError(string $fileName): PhpParserError
    {
        return new PhpParserError(PhpParserError::TYPE_ERROR, 'error', $fileName);
    }
}
