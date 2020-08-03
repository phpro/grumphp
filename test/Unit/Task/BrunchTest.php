<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Brunch;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class BrunchTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Brunch(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'task' => 'build',
                'env' => 'production',
                'jobs' => 4,
                'debug' => false,
                'triggered_by' => ['js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss'],
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
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('brunch', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('brunch', $this->mockProcess(0));
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
            $this->mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '4',
            ]
        ];
        yield 'task' => [
            [
                'task' => 'sleep',
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'sleep',
                '--env',
                'production',
                '--jobs',
                '4',
            ]
        ];
        yield 'env' => [
            [
                'env' => 'acceptation',
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'acceptation',
                '--jobs',
                '4',
            ]
        ];
        yield 'jobs' => [
            [
                'jobs' => 10,
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '10',
            ]
        ];
        yield 'debug' => [
            [
                'debug' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '4',
                '--debug',
            ]
        ];
    }
}
