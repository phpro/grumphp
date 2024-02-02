<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityCheckerComposeraudit;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class SecurityCheckerComposerauditTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new SecurityCheckerComposeraudit(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'format' => null,
                'locked' => true,
                'no_dev' => false,
                'run_always' => false,
                'working_dir' => './',
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
                $this->mockProcessBuilder('composer', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
            }
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            $this->mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
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
            'composer',
            [
                'audit',
                '--locked',
                '--working-dir=./',
            ]
        ];

        yield 'format' => [
            [
                'format' => 'json',
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--format=json',
                '--locked',
                '--working-dir=./',
            ]
        ];

        yield 'locked' => [
            [
                'locked' => false,
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--working-dir=./',
            ]
        ];

        yield 'no-dev' => [
            [
                'no_dev' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--locked',
                '--no-dev',
                '--working-dir=./',
            ]
        ];

        yield 'working-dir' => [
            [
                'working_dir' => './',
            ],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--locked',
                '--working-dir=./',
            ]
        ];
    }
}
