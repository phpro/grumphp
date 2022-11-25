<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Rector;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RectorTest extends AbstractExternalTaskTestCase
{
    /**
     * @var ProcessFormatterInterface|ObjectProphecy
     */
    protected $formatter;

    protected function provideTask(): TaskInterface
    {
        $this->formatter = $this->prophesize(RawProcessFormatter::class);

        return new Rector(
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
                'triggered_by' => ['php'],
                'ignore_patterns' => [],
                'clear_cache' => true,
                'no_diffs' => false,
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
                $this->mockProcessBuilder('rector', $process = $this->mockProcess(1));

                $this->formatter->format($process)->willReturn($message = 'message');
            },
            'message',
            FixableTaskResult::class
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('rector', $this->mockProcess(0));
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
            'rector',
            [
                'process',
                '--dry-run',
                '--no-progress-bar',
                '--clear-cache',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'rector-config.php',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--no-progress-bar',
                '--config=rector-config.php',
                '--clear-cache',
            ]
        ];
        yield 'no-clear-cache' => [
            [
                'clear_cache' => false,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--no-progress-bar',
            ]
        ];
        yield 'no-diffs' => [
            [
                'no_diffs' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--no-progress-bar',
                '--clear-cache',
                '--no-diffs'
            ]
        ];
    }
}
