<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task\Git;

use GrumPHP\IO\IOInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Git\Blacklist;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class BlacklistTest extends AbstractExternalTaskTestCase
{
    /**
     * @var IOInterface|ObjectProphecy
     */
    private $IO;

    protected function provideTask(): TaskInterface
    {
        $this->IO = $this->prophesize(IOInterface::class);
        $this->IO->isDecorated()->willReturn(true);

        return new Blacklist(
            $this->processBuilder->reveal(),
            $this->formatter->reveal(),
            $this->IO->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'keywords' => [],
                'whitelist_patterns' => [],
                'triggered_by' => ['php'],
                'regexp_type' => 'G',
                'match_word' => false
            ]
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            false,
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
        yield 'exitCode0' => [
            [
                'keywords' => ['a'],
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('git', $process = $this->mockProcess(0));
                $this->formatter->format($process)->willReturn('grep contains blacklisted word');
            },
            'You have blacklisted keywords in your commit:'.PHP_EOL.'grep contains blacklisted word'
        ];
        yield 'exitCodeAbove1' => [
            [
                'keywords' => ['a'],
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('git', $process = $this->mockProcess(2, 'output', 'nope'));
            },
            'Something went wrong:'.PHP_EOL.'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [
                'keywords' => ['a'],
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('git', $this->mockProcess(1));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [
                'keywords' => ['a'],
            ],
            $this->mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [
                'keywords' => ['a'],
            ],
            $this->mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
        yield 'no-files-after-whitelist' => [
            [
                'keywords' => ['a'],
                'whitelist_patterns' => ['src/'],
            ],
            $this->mockContext(RunContext::class, ['test/file.php']),
            function () {}
        ];
        yield 'no-keywords' => [
            [
                'keywords' => [],
            ],
            $this->mockContext(RunContext::class, ['file.php']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [
                'keywords' => ['keyword1', 'keyword2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'git',
            [
                'grep',
                '--cached',
                '-n',
                '--break',
                '--heading',
                '--color',
                '-G',
                '-e',
                'keyword1',
                '-e',
                'keyword2',
                'hello.php',
                'hello2.php',
            ],
            $this->mockProcess(1)
        ];
        yield 'regexp-type' => [
            [
                'keywords' => ['keyword'],
                'regexp_type' => 'P',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'git',
            [
                'grep',
                '--cached',
                '-n',
                '--break',
                '--heading',
                '--color',
                '-P',
                '-e',
                'keyword',
                'hello.php',
                'hello2.php',
            ],
            $this->mockProcess(1)
        ];

        yield 'match_word' => [
            [
                'keywords' => ['keyword'],
                'match_word' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'git',
            [
                'grep',
                '--cached',
                '-n',
                '--break',
                '--heading',
                '--word-regexp',
                '--color',
                '-G',
                '-e',
                'keyword',
                'hello.php',
                'hello2.php',
            ],
            $this->mockProcess(1)
        ];
    }
}
