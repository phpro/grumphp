<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpMd;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpMdTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpMd(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'whitelist_patterns' => [],
                'exclude' => [],
                'report_format' => 'text',
                'ruleset' => ['cleancode', 'codesize', 'naming'],
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
                $this->mockProcessBuilder('phpmd', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('phpmd', $this->mockProcess(0));
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
        yield 'no-files-after-whitelist' => [
            [
                'whitelist_patterns' => ['src/'],
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
            'phpmd',
            [
                'hello.php,hello2.php',
                'text',
                'cleancode,codesize,naming',
            ]
        ];

        yield 'excludes' => [
            [
                'exclude' => ['hello.php', 'hello2.php'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'hello.php,hello2.php',
                'text',
                'cleancode,codesize,naming',
                '--exclude',
                'hello.php,hello2.php',
            ]
        ];

        yield 'rulesets' => [
            [
                'ruleset' => ['cleancode'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'hello.php,hello2.php',
                'text',
                'cleancode',
            ]
        ];

        yield 'report_formats' => [
            [
                'report_format' => 'ansi',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmd',
            [
                'hello.php,hello2.php',
                'ansi',
                'cleancode,codesize,naming',
            ]
        ];
    }
}
