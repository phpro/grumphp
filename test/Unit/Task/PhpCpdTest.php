<?php

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCpd;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpCpdTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpCpd(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=*.php',
                '.',
            ],
        ];

        yield 'directory' => [
            [
                'directory' => ['folder-1', 'folder-2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=*.php',
                'folder-1',
                'folder-2',
            ],
        ];

        yield 'exclude' => [
            [
                'exclude' => ['folder-1', 'folder-2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=folder-1',
                '--exclude=folder-2',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=*.php',
                '.',
            ],
        ];

        yield 'fuzzy' => [
            [
                'fuzzy' => true
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=*.php',
                '--fuzzy',
                '.',
            ],
        ];

        yield 'min_lines' => [
            [
                'min_lines' => 10
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=10',
                '--min-tokens=70',
                '--suffix=*.php',
                '.',
            ],
        ];

        yield 'min_tokens' => [
            [
                'min_tokens' => 10
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=10',
                '--suffix=*.php',
                '.',
            ],
        ];

        yield 'triggered_by' => [
            [
                'triggered_by' => ['php', 'html']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=*.php,*.html',
                '.',
            ],
        ];
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'directory' => ['.'],
                'exclude' => ['vendor'],
                'fuzzy' => false,
                'min_lines' => 5,
                'min_tokens' => 70,
                'triggered_by' => ['php'],
            ],
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            $this->mockContext(RunContext::class),
        ];

        yield 'pre-commit-context' => [
            true,
            $this->mockContext(GitPreCommitContext::class),
        ];

        yield 'other' => [
            false,
            $this->mockContext(),
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcpd', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            TaskResult::class,
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcpd', $this->mockProcess(0));
            },
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {
            },
        ];

        yield 'no-files-after-triggered-by' => [
            [],
            $this->mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {
            },
        ];
    }
}
