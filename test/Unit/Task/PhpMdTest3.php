<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpMd3;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpMdTest3 extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpMd3(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'whitelist_patterns' => [],
                'exclude' => [],
                'report_format' => 'text',
                'ruleset' => ['phpmd.xml.dist'],
                'triggered_by' => ['php'],
            ]
        ];

        yield 'invalidcase' => [
            [
                'whitelist_patterns' => 'thisisnotanarray'
            ],
            null
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
                $this->mockProcessBuilder('phpmd', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('phpmd', self::mockProcess(0));
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
        yield 'no-files-after-whitelist' => [
            [
                'whitelist_patterns' => ['src/'],
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
            'phpmd',
            [
                'analyze',
                '--format=text',
                '--ruleset=phpmd.xml.dist',
                '--suffixes=php',
                'hello.php',
                'hello2.php',
            ]
        ];

        yield 'excludes' => [
            [
                'exclude' => ['hello.php', 'hello2.php'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'analyze',
                '--format=text',
                '--ruleset=phpmd.xml.dist',
                '--exclude=hello.php',
                '--exclude=hello2.php',
                '--suffixes=php',
                'hello.php',
                'hello2.php',
            ]
        ];

        yield 'rulesets' => [
            [
                'ruleset' => ['cleancode'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'analyze',
                '--format=text',
                '--ruleset=cleancode',
                '--suffixes=php',
                'hello.php',
                'hello2.php',
            ]
        ];

        yield 'report_formats' => [
            [
                'report_format' => 'ansi',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'analyze',
                '--format=ansi',
                '--ruleset=phpmd.xml.dist',
                '--suffixes=php',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
