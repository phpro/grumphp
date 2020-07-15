<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityChecker;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class SecurityCheckerTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new SecurityChecker(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'lockfile' => './composer.lock',
                'format' => null,
                'end_point' => null,
                'timeout' => null,
                'run_always' => false,
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
            $this->mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('security-checker', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('security-checker', $this->mockProcess(0));
            }
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            $this->mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('security-checker', $this->mockProcess(0));
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
        yield 'no-composer-file' => [
            [],
            $this->mockContext(RunContext::class, ['thisisnotacomposerfile.lock']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'security-checker',
            [
                'security:check',
                './composer.lock',
            ]
        ];

        yield 'endpoint' => [
            [
                'end_point' => $endpoint = 'http://myserver.com',
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'security-checker',
            [
                'security:check',
                './composer.lock',
                '--end-point='.$endpoint
            ]
        ];

        yield 'timeout' => [
            [
                'timeout' => 2,
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'security-checker',
            [
                'security:check',
                './composer.lock',
                '--timeout=2'
            ]
        ];
    }
}
