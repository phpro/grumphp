<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Psalm;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PsalmTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Psalm(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => null,
                'ignore_patterns' => [],
                'no_cache' => false,
                'report' => null,
                'output_format' => null,
                'threads' => null,
                'triggered_by' => ['php'],
                'show_info' => false,
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
                $this->mockProcessBuilder('psalm', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('psalm', $this->mockProcess(0));
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
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
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
            'psalm',
            [
                '--show-info=false',
            ]
        ];
        yield 'output-format' => [
            [
                'output_format' => 'emacs',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--output-format=emacs',
                '--show-info=false',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'psalm.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--config=psalm.xml',
                '--show-info=false',
            ]
        ];
        yield 'report' => [
            [
                'report' => 'reportfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--report=reportfile',
                '--show-info=false',
            ]
        ];
        yield 'no-cache' => [
            [
                'no_cache' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--no-cache',
                '--show-info=false',
            ]
        ];
        yield 'threads' => [
            [
                'threads' => 10,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--threads=10',
                '--show-info=false',
            ]
        ];
        yield 'show-info' => [
            [
                'show_info' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--show-info=true',
            ]
        ];
        yield 'with-files' => [
            [],
            $this->mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--show-info=false',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
