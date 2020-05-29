<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Php7cc;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class Php7ccTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Php7cc(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'exclude' => [],
                'level' => null,
                'triggered_by' => ['php'],
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
        yield 'exitCode1ErrorOutput' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $process = $this->mockProcess(1, 'File: hello.php ....');
                $this->mockProcessBuilder('php7cc', $process);
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0NoOutput' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('php7cc', $this->mockProcess(0, ''));
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
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php7cc',
            [
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'level' => [
            [
                'level' => 'error'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php7cc',
            [
                '--level=error',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'except' => [
            [
                'exclude' => ['exclude1', 'exclude2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php7cc',
            [
                '--except',
                'exclude1',
                '--except',
                'exclude2',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
