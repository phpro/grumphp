<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhanTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Phan(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'output_mode' => 'text',
                'output' => null,
                'config_file' => '.phan/config.php',
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
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phan', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('phan', $this->mockProcess(0));
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
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'text',
            ]
        ];
        yield 'config-file' => [
            [
                'config_file' => 'config.php',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                'config.php',
                '--output-mode',
                'text',
            ]
        ];
        yield 'output-mode' => [
            [
                'output_mode' => 'json',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'json',
            ]
        ];
        yield 'output' => [
            [
                'output' => 'file.txt',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'text',
                '--output',
                'file.txt'
            ]
        ];
    }
}
