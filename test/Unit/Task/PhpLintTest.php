<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpLint;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpLintTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpLint(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'php_executable' => null,
                'jobs' => null,
                'short_open_tag' => false,
                'exclude' => [],
                'ignore_patterns' => [],
                'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
            ]
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            self::mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            self::mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            self::mockContext()
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('parallel-lint', $process = self::mockProcessWithStdIn(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('parallel-lint', self::mockProcessWithStdIn(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
        yield 'php_executable_null' => [
            [
                'php_executable' => null,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
        yield 'php_executable' => [
            [
                'php_executable' => '/usr/bin/php',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '-p',
                '/usr/bin/php',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
        yield 'jobs' => [
            [
                'jobs' => 10
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '-j',
                '10',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
        yield 'short_open_tag' => [
            [
                'short_open_tag' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--short',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
        yield 'exlude' => [
            [
                'exclude' => ['exclude1', 'exclude2'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'parallel-lint',
            [
                '--no-colors',
                '--exclude',
                'exclude1',
                '--exclude',
                'exclude2',
                '--stdin',
            ],
            self::mockProcessWithStdIn(0)
        ];
    }
}
