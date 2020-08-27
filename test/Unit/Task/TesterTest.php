<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\Tester;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class TesterTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Tester(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'path' => '.',
                'always_execute' => false,
                'log' => null,
                'show_information_about_skipped_tests' => false,
                'stop_on_fail' => false,
                'parallel_processes' => null,
                'output' => null,
                'temp' => null,
                'setup' => null,
                'colors' => null,
                'coverage' => null,
                'coverage_src' => null,
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
            $this->mockContext(RunContext::class, ['helloTest.php']),
            function () {
                $this->mockProcessBuilder('tester', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['helloTest.php']),
            function () {
                $this->mockProcessBuilder('tester', $this->mockProcess(0));
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
        yield 'no-files-after-name_match' => [
            [],
            $this->mockContext(RunContext::class, ['notatestfile.php']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
            ]
        ];
        yield 'path' => [
            [
                'path' => 'src',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                'src',
            ]
        ];
        yield 'always_execute_with_files' => [
            [
                'always_execute' => true,
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
            ]
        ];
        yield 'always_execute_without_files' => [
            [
                'always_execute' => true,
            ],
            $this->mockContext(RunContext::class, []),
            'tester',
            [
                '.',
            ]
        ];
        yield 'log' => [
            [
                'log' => 'logfile.txt',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--log',
                'logfile.txt',
            ]
        ];
        yield 'show_information_about_skipped_tests' => [
            [
                'show_information_about_skipped_tests' => true,
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-s',
            ]
        ];
        yield 'stop_on_fail' => [
            [
                'stop_on_fail' => true,
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--stop-on-fail',
            ]
        ];
        yield 'parallel_processes' => [
            [
                'parallel_processes' => 2,
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-j',
                '2'
            ]
        ];
        yield 'output' => [
            [
                'output' => 'console',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-o',
                'console',
            ]
        ];
        yield 'temp' => [
            [
                'temp' => '/tmp',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--temp',
                '/tmp',
            ]
        ];
        yield 'setup' => [
            [
                'setup' => 'setup.php',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--setup',
                'setup.php',
            ]
        ];
        yield 'colors' => [
            [
                'colors' => 4,
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--colors',
                '4',
            ]
        ];
        yield 'coverage' => [
            [
                'coverage' => 'coverageFile',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--coverage',
                'coverageFile',
            ]
        ];
        yield 'coverage_src' => [
            [
                'coverage_src' => 'coverageSrdFile',
            ],
            $this->mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--coverage-src',
                'coverageSrdFile',
            ]
        ];
    }
}
