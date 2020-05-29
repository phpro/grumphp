<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Kahlan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class KahlanTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Kahlan(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => 'kahlan-config.php',
                'src' => ['src'],
                'spec' => ['spec'],
                'pattern' => '*Spec.php',
                'reporter' => null,
                'coverage' => null,
                'clover' => null,
                'istanbul' => null,
                'lcov' => null,
                'ff' => 0,
                'no_colors' => false,
                'no_header' => false,
                'include' => ['*'],
                'exclude' => [],
                'persistent' => true,
                'cc' => false,
                'autoclear' => [
                    'Kahlan\Plugin\Monkey',
                    'Kahlan\Plugin\Call',
                    'Kahlan\Plugin\Stub',
                    'Kahlan\Plugin\Quit',
                ],
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
                $this->mockProcessBuilder('kahlan', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('kahlan', $this->mockProcess(0));
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

    /**
     * TODO : This task seems to be bogus ... Needs some fixin
     */
    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'kahlan',
            [
                'config',
                'src',
                'src',
                'spec',
                'spec',
                '--pattern',
                '--persistent',
                '--autoclear',
            ]
        ];
    }
}
