<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Codeception;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class CodeceptionTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Codeception(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config_file' => null,
                'suite' => null,
                'test' => null,
                'fail_fast' => false,
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
                $this->mockProcessBuilder('codecept', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('codecept', $this->mockProcess(0));
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
            'codecept',
            [
                'run',
            ]
        ];
        yield 'config' => [
            [
                'config_file' => 'configfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--config=configfile'
            ]
        ];
        yield 'fail-fast' => [
            [
                'fail_fast' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--fail-fast'
            ]
        ];
        yield 'suite' => [
            [
                'suite' => 'suite',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                'suite'
            ]
        ];
        yield 'test' => [
            [
                'test' => 'test',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                'test'
            ]
        ];
    }
}
