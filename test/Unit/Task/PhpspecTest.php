<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpspec;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpspecTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Phpspec(
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
                'format' => null,
                'stop_on_failure' => false,
                'verbose' => false,
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
                $this->mockProcessBuilder('phpspec', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('phpspec', $this->mockProcess(0));
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
            'phpspec',
            [
                'run',
                '--no-interaction',
            ]
        ];
        yield 'config' => [
            [
                'config_file' => 'configile.yml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--config=configile.yml'
            ]
        ];
        yield 'format' => [
            [
                'format' => 'dot',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--format=dot'
            ]
        ];
        yield 'stop-on-failure' => [
            [
                'stop_on_failure' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--stop-on-failure'
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--verbose'
            ]
        ];
    }
}
