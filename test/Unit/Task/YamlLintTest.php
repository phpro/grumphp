<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Linter\Yaml\YamlLintError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\YamlLint;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class YamlLintTest extends AbstractTaskTestCase
{
    /**
     * @var YamlLinter|ObjectProphecy
     */
    private $linter;

    protected function provideTask(): TaskInterface
    {
        $this->linter = $this->prophesize(YamlLinter::class);
        $this->linter->isInstalled()->willReturn(true);

        return new YamlLint($this->linter->reveal());
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'ignore_patterns' => [],
                'object_support' => false,
                'exception_on_invalid_type' => false,
                'parse_constant' => false,
                'parse_custom_tags' => false,
                'whitelist_patterns' => [],
            ]
        ];

        yield 'invalidcase' => [
            [
                'whitelist_patterns' => 'thisisnotanarray'
            ],
            null
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
        yield 'exception' => [
            [],
            $this->mockContext(RunContext::class, ['hello.yml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willThrow(new RuntimeException('nope'));
            },
            'nope'
        ];

        yield 'lint-errors-on-one-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.yml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.yml'),
                        $this->createLintError('hello.yml'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.yml'),
                $this->createLintError('hello.yml'),
            ]))
        ];

        yield 'lint-errors-on-multiple-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.yml', 'world.yaml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.yml'),
                        $this->createLintError('hello.yml'),
                    ])
                );
                $this->linter->lint($context->getFiles()[1])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('world.yaml'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.yml'),
                $this->createLintError('hello.yml'),
                $this->createLintError('world.yml'),
            ]))
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'no-lint-errors' => [
            [],
            $this->mockContext(RunContext::class, ['hello.yml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-on-multiple-files' => [
            [],
            $this->mockContext(RunContext::class, ['hello.yml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-with-non-default-linter-options' => [
            [
                'object_support' => true,
                'exception_on_invalid_type' => true,
                'parse_constant' => true,
                'parse_custom_tags' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.yml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['src/'],
            ],
            $this->mockContext(RunContext::class, ['src/hello.yml']),
            function (array $options) {
                $this->assumeLinterConfig($options);
                $this->linter->lint(Argument::cetera())->shouldNotBeCalled();
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
        yield 'no-files-after-whitelist' => [
            [
                'whitelist_patterns' => ['src/'],
            ],
            $this->mockContext(RunContext::class, ['test/file.yml']),
            function () {}
        ];
    }

    private function assumeLinterConfig(array $options)
    {
        $this->linter->setObjectSupport($options['object_support'])->shouldBeCalled();
        $this->linter->setExceptionOnInvalidType($options['exception_on_invalid_type'])->shouldBeCalled();
        $this->linter->setParseCustomTags($options['parse_custom_tags'])->shouldBeCalled();
        $this->linter->setParseConstants($options['parse_constant'])->shouldBeCalled();
    }

    private function createLintError(string $fileName): YamlLintError
    {
        return new YamlLintError(LintError::TYPE_ERROR, 'error', $fileName);
    }
}
