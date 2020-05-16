<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\ComposerRequireChecker;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ComposerRequireCheckerTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new ComposerRequireChecker(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'composer_file' => 'composer.json',
                'config_file' => null,
                'ignore_parse_errors' => false,
                'triggered_by' => ['composer.json', 'composer.lock', '*.php'],
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
                $this->mockProcessBuilder('composer-require-checker', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('composer-require-checker', $this->mockProcess(0));
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
            'composer-require-checker',
            [
                'check',
                '--no-interaction',
                'composer.json',
            ]
        ];
        yield 'composer-file' => [
            [
                'composer_file' => 'src/composer.json',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--no-interaction',
                'src/composer.json',
            ]
        ];
        yield 'config-file' => [
            [
                'config_file' => 'configfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--config-file=configfile',
                '--no-interaction',
                'composer.json',
            ]
        ];
        yield 'ignore-parse-errors' => [
            [
                'ignore_parse_errors' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--ignore-parse-errors',
                '--no-interaction',
                'composer.json',
            ]
        ];
    }
}
