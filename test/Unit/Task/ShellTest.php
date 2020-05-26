<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Shell;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ShellTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Shell(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'scripts' => [],
                'triggered_by' => ['php'],
            ]
        ];
        yield 'string_script' => [
            [
                'scripts' => ['phpunit'],
            ],
            [
                'scripts' => [
                    ['phpunit']
                ],
                'triggered_by' => ['php'],
            ]
        ];
        yield 'array_script' => [
            [
                'scripts' => [
                    ['phpunit', 'tests']
                ],
            ],
            [
                'scripts' => [
                    ['phpunit', 'tests']
                ],
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
        yield 'exitCode1On1Task' => [
            [
                'scripts' => ['phpunit']
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'exitCode1On2Task' => [
            [
                'scripts' => [
                    'phpunit',
                    'phpspec'
                ]
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'.PHP_EOL.'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'noScript' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $this->mockProcess(0));
            }
        ];
        yield 'exitCode0On1Task' => [
            [
                'scripts' => ['phpunit']
            ],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $this->mockProcess(0));
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
            $this->mockContext(RunContext::class, ['notatwigfile.txt']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'string-script' => [
            [
                'scripts' => ['phpunit']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'sh',
            [
                'phpunit',
            ]
        ];

        yield 'array-script' => [
            [
                'scripts' => [['phpunit', 'tests']]
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'sh',
            [
                'phpunit',
                'tests'
            ]
        ];
    }
}
