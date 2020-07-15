<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpLint;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpLintTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpLint(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'jobs' => null,
                'short_open_tag' => false,
                'exclude' => [],
                'ignore_patterns' => [],
                'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
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
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('parallel-lint', $process = $this->mockProcessWithStdIn(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('parallel-lint', $this->mockProcessWithStdIn(0));
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
            $this->mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            $this->mockContext(RunContext::class, ['test/file.php']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--stdin',
            ],
            $this->mockProcessWithStdIn(0)
        ];
        yield 'jobs' => [
            [
                'jobs' => 10
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '-j',
                '10',
                '--stdin',
            ],
            $this->mockProcessWithStdIn(0)
        ];
        yield 'short_open_tag' => [
            [
                'short_open_tag' => true
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--short',
                '--stdin',
            ],
            $this->mockProcessWithStdIn(0)
        ];
        yield 'exlude' => [
            [
                'exclude' => ['exclude1', 'exclude2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--exclude',
                'exclude1',
                '--exclude',
                'exclude2',
                '--stdin',
            ],
            $this->mockProcessWithStdIn(0)
        ];
    }
}
