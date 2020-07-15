<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Progpilot;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ProgpilotTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Progpilot(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config_file'  => '.progpilot/configuration.yml',
                'triggered_by' => ['php']
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
                $this->mockProcessBuilder('progpilot', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('progpilot', $this->mockProcess(0));
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
            'progpilot',
            [
                '--configuration',
                '.progpilot/configuration.yml',
                'hello.php',
                'hello2.php',
            ]
        ];

        yield 'config_file' => [
            [
                'config_file' => 'config.yml'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'progpilot',
            [
                '--configuration',
                'config.yml',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
