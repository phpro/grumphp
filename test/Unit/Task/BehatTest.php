<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Behat;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class BehatTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Behat(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => null,
                'format' => null,
                'suite' => null,
                'profile' => null,
                'stop_on_failure' => false,
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
                $this->mockProcessBuilder('behat', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('behat', $this->mockProcess(0));
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
            'behat',
            []
        ];
        yield 'config' => [
            [
                'config' => 'configfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--config=configfile'
            ]
        ];
        yield 'format' => [
            [
                'format' => 'myformat',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--format=myformat'
            ]
        ];
        yield 'suite' => [
            [
                'suite' => 'suite',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--suite=suite'
            ]
        ];
        yield 'profile' => [
            [
                'profile' => 'profile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--profile=profile'
            ]
        ];
        yield 'stop-on-failure' => [
            [
                'stop_on_failure' => true
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--stop-on-failure'
            ]
        ];
    }
}
