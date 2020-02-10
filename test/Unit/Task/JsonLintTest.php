<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Linter\Json\JsonLintError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\JsonLint;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class JsonLintTest extends AbstractTaskTestCase
{
    /**
     * @var JsonLinter|ObjectProphecy
     */
    private $linter;

    protected function provideTask(): TaskInterface
    {
        $this->linter = $this->prophesize(JsonLinter::class);
        $this->linter->isInstalled()->willReturn(true);

        return new JsonLint($this->linter->reveal());
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'ignore_patterns' => [],
                'detect_key_conflicts' => false,
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
        yield 'exception' => [
            [],
            $this->mockContext(RunContext::class, ['hello.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willThrow(new RuntimeException('nope'));
            },
            'nope'
        ];

        yield 'lint-errors-on-one-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()->first())->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.json'),
                        $this->createLintError('hello.json'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.json'),
                $this->createLintError('hello.json'),
            ]))
        ];

        yield 'lint-errors-on-multiple-file' => [
            [],
            $this->mockContext(RunContext::class, ['hello.json', 'world.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('hello.json'),
                        $this->createLintError('hello.json'),
                    ])
                );
                $this->linter->lint($context->getFiles()[1])->willReturn(
                    new LintErrorsCollection([
                        $this->createLintError('world.json'),
                    ])
                );
            },
            (string) (new LintErrorsCollection([
                $this->createLintError('hello.json'),
                $this->createLintError('hello.json'),
                $this->createLintError('world.json'),
            ]))
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'no-lint-errors' => [
            [],
            $this->mockContext(RunContext::class, ['hello.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-on-multiple-files' => [
            [],
            $this->mockContext(RunContext::class, ['hello.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-lint-errors-with-non-default-linter-options' => [
            [
                'detect_key_conflicts' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.json']),
            function (array $options, ContextInterface $context) {
                $this->assumeLinterConfig($options);
                $this->linter->lint($context->getFiles()[0])->willReturn(new LintErrorsCollection([]));
            }
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['src/'],
            ],
            $this->mockContext(RunContext::class, ['src/hello.json']),
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
    }

    private function assumeLinterConfig(array $options)
    {
        $this->linter->setDetectKeyConflicts($options['detect_key_conflicts'])->shouldBeCalled();
    }

    private function createLintError(string $fileName): JsonLintError
    {
        return new JsonLintError(LintError::TYPE_ERROR, 'error', $fileName, 0);
    }
}
