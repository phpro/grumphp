<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Xml\XmlLinter;
use GrumPHP\Linter\Xml\XmlLintError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\XmlLint;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class XmlLintTest extends AbstractTaskTestCase
{
    /**
     * @var XmlLinter|ObjectProphecy
     */
    private $linter;

    protected function provideTask(): TaskInterface
    {
        $this->linter = $this->prophesize(XmlLinter::class);
        $this->linter->isInstalled()->willReturn(true);

        return new XmlLint($this->linter->reveal());
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'ignore_patterns' => [],
                'load_from_net' => false,
                'x_include' => false,
                'dtd_validation' => false,
                'scheme_validation' => false,
                'triggered_by' => ['xml'],
            ]
        ];

        yield 'invalidcase' => [
            [
                'ignore_patterns' => 'thisisnotanarray'
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
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willThrow(new RuntimeException('nope'));
            },
            'nope'
        ];

        yield 'lint-errors-on-one-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.xml'),
                        $this->createLintError('hello.xml'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.xml'),
                $this->createLintError('hello.xml'),
            ]))
        ];

        yield 'lint-errors-on-multiple-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.xml', 'world.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.xml'),
                        $this->createLintError('hello.xml'),
                    ])
                );
                $this->linter->lint($context->getFiles()[1])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('world.xml'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.xml'),
                $this->createLintError('hello.xml'),
                $this->createLintError('world.xml'),
            ]))
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'no-lint-errors' => [
            [],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-on-multiple-files' => [
            [],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-with-non-default-linter-options' => [
            [
                'load_from_net' => true,
                'x_include' => true,
                'dtd_validation' => true,
                'scheme_validation' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['src/'],
            ],
            $this->mockContext(RunContext::class, ['src/hello.xml']),
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
            $this->mockContext(RunContext::class, ['notaxmlfile.txt']),
            function () {}
        ];
    }

    private function assumeLinterConfig(array $options)
    {
        $this->linter->setLoadFromNet($options['load_from_net'])->shouldBeCalled();
        $this->linter->setXInclude($options['x_include'])->shouldBeCalled();
        $this->linter->setDtdValidation($options['dtd_validation'])->shouldBeCalled();
        $this->linter->setSchemeValidation($options['scheme_validation'])->shouldBeCalled();

    }

    private function createLintError(string $fileName): XmlLintError
    {
        return new XmlLintError(LintError::TYPE_ERROR, 0, 'error', $fileName, 10, 20);
    }
}
