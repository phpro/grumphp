<?php

declare(strict_types=1);

namespace GrumPHP\Test\Unit\Task;

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

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                // Task config options
                'bin' => null,
                'triggered_by' => ['js', 'jsx', 'ts', 'tsx', 'vue'],
                'whitelist_patterns' => null,

                // ESLint native config options
                'config' => null,
                'ignore_path' => null,
                'debug' => false,
                'format' => null,
                'max_warnings' => null,
                'no_eslintrc' => false,
                'quiet' => false,
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
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('eslint', $process = $this->mockProcess(1));

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
            $this->mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('eslint', $this->mockProcess(0));
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
            $this->mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
        yield 'no-files-after-whitelist-patterns' => [
            [
                'whitelist_patterns' => ['/^resources\/js\/(.*)/'],
            ],
            $this->mockContext(RunContext::class, ['resources/dont/find/this/file.js']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'bin' => [
            [
                'bin' => 'node_modules/.bin/eslint',
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--ignore-path=.eslintignore',
                'hello.js',
                'hello2.js',
            ]
        ];
        yield 'debug' => [
            [
                'debug' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
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
            $this->mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'eslint',
            [
                '--max-warnings=10',
                'hello.js',
                'hello2.js',
            ]
        ];
    }
}
