<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpMnd;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpMndTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpMnd(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'directory' => '.',
                'whitelist_patterns' => [],
                'exclude' => [],
                'exclude_name' => [],
                'exclude_path' => [],
                'extensions' => [],
                'hint' => false,
                'ignore_funcs' => [],
                'ignore_numbers' => [],
                'ignore_strings' => [],
                'strings' => false,
                'triggered_by' => ['php'],
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
                $this->mockProcessBuilder('phpmnd', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('phpmnd', $this->mockProcess(0));
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
            'phpmnd',
            [
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'directory' => [
            [
                'directory' => 'directory'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                'directory',
            ]
        ];
        yield 'exclude' => [
            [
                'exclude' => ['exclude1', 'exclude2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude=exclude1',
                '--exclude=exclude2',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'exclude_name' => [
            [
                'exclude_name' => ['exclude1', 'exclude2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude-file=exclude1',
                '--exclude-file=exclude2',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'exclude_path' => [
            [
                'exclude_path' => ['exclude1', 'exclude2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude-path=exclude1',
                '--exclude-path=exclude2',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'extensions' => [
            [
                'extensions' => ['php', 'phtml']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--extensions=php,phtml',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'hint' => [
            [
                'hint' => true
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--hint',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'ignore_funcs' => [
            [
                'ignore_funcs' => ['intval', 'floatval', 'strval']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-funcs=intval,floatval,strval',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'ignore_numbers' => [
            [
                'ignore_numbers' => [0,1],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-numbers=0,1',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'ignore_strings' => [
            [
                'ignore_strings' => ['0', '1']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-strings=0,1',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'strings' => [
            [
                'strings' => true
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--strings',
                '--suffixes=php',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
        yield 'triggered-by' => [
            [
                'triggered_by' => ['php', 'phtml']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--suffixes=php,phtml',
                '--non-zero-exit-on-violation',
                '.',
            ]
        ];
    }
}
