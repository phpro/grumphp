<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\ESLint;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ESLintTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new ESLint(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                // Task config options
                'bin' => null,
                'triggered_by' => ['js', 'jsx', 'ts', 'tsx', 'vue'],
                'whitelist_patterns' => [],

                // ESLint native config options
                'config' => null,
                'ignore_path' => null,
                'cache' => null,
                'cache_location' => null,
                'debug' => false,
                'format' => null,
                'max_warnings' => null,
                'no_eslintrc' => false,
                'quiet' => false,
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
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('eslint', $process = self::mockProcess(1));

                $this->formatter->format($process)->willReturn($message = 'message');
            },
            'message',
            FixableTaskResult::class
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('eslint', self::mockProcess(0));
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
            self::mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
        yield 'no-files-after-whitelist-patterns' => [
            [
                'whitelist_patterns' => ['/^resources\/js\/(.*)/'],
            ],
            self::mockContext(RunContext::class, ['resources/dont/find/this/file.js']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'bin' => [
            [
                'bin' => 'node_modules/.bin/eslint',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                'node_modules/.bin/eslint',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'config' => [
            [
                'config' => '.eslintrc.json',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--config=.eslintrc.json',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'ignore_path' => [
            [
                'ignore_path' => '.eslintignore',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--ignore-path=.eslintignore',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'cache' => [
            [
                'cache' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--cache',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'cache_location' => [
            [
                'cache_location' => 'path/to/cache',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--cache-location=path/to/cache',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'debug' => [
            [
                'debug' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--debug',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'format' => [
            [
                'format' => 'table',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--format=table',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'no_eslintrc' => [
            [
                'no_eslintrc' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--no-eslintrc',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'quiet' => [
            [
                'quiet' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--quiet',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'max_warnings' => [
            [
                'max_warnings' => 10,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--max-warnings=10',
                'hello.js',
                'hello2.js',
            ]
        ];
    }
}
