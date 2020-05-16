<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\NpmScript;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\Process;

class NpmScriptTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new NpmScript(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [
                'script' => 'script',
            ],
            [
                'script' => 'script',
                'triggered_by' => ['js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss'],
                'working_directory' => './',
                'is_run_task' => false,
                'silent' => false,
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
            [
                'script' => 'script'
            ],
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('npm', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [
                'script' => 'script'
            ],
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('npm', $this->mockProcess(0));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [
                'script' => 'script'
            ],
            $this->mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [
                'script' => 'script'
            ],
            $this->mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [
                'script' => 'script'
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'script'
            ]
        ];
        yield 'run-task' => [
            [
                'script' => 'script',
                'is_run_task' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'run',
                'script'
            ]
        ];
        yield 'silent' => [
            [
                'script' => 'script',
                'silent' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'script',
                '--silent',
            ]
        ];
    }
}
