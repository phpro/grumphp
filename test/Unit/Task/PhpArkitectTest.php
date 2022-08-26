<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpArkitect;
use GrumPHP\Task\PhpStan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpArkitectTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpArkitect(
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
                'target_php_version' => null,
                'stop_on_failure' => null,
            ],
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
                $this->mockProcessBuilder('phparkitect', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('phparkitect', $this->mockProcess(0));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        return [];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--no-ansi',
                '--no-interaction'
            ]
        ];
        yield 'config' => [
            [
                'config' => 'phparkitect.php'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--config=phparkitect.php',
                '--no-ansi',
                '--no-interaction'
            ]
        ];
        yield 'target_php_version' => [
            [
                'target_php_version' => '8.1'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--target-php-version=8.1',
                '--no-ansi',
                '--no-interaction'
            ]
        ];
        yield 'stop_on_failure' => [
            [
                'stop_on_failure' => TRUE
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--stop-on-failure',
                '--no-ansi',
                '--no-interaction'
            ]
        ];
    }
}
