<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Paratest;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ParatestTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Paratest(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'group' => [],
                'config' => null,
                'processes' => null,
                'functional' => false,
                'phpunit' => null,
                'configuration' => null,
                'always_execute' => false,
                'runner' => null,
                'coverage-clover' => null,
                'coverage-html' => null,
                'coverage-php' => null,
                'coverage-xml' => null,
                'log-junit' => null,
                'testsuite' => null,
                'verbose' => false,
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
                $this->mockProcessBuilder('paratest', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('paratest', $this->mockProcess(0));
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
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            []
        ];
        yield 'processes' => [
            [
                'processes' => 10,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '-p=10',
            ]
        ];
        yield 'functional' => [
            [
                'functional' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '-f',
            ]
        ];
        yield 'configuration' => [
            [
                'configuration' => 'phpunit.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '-c=phpunit.xml',
            ]
        ];
        yield 'runner' => [
            [
                'runner' => 'WrapperRunner',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--runner=WrapperRunner',
            ]
        ];
        yield 'coverage-clover' => [
            [
                'coverage-clover' => 'clover.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-clover=clover.xml',
            ]
        ];
        yield 'coverage-html' => [
            [
                'coverage-html' => 'coverage.html',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-html=coverage.html',
            ]
        ];
        yield 'coverage-php' => [
            [
                'coverage-php' => 'coverage.php',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-php=coverage.php',
            ]
        ];
        yield 'coverage-xml' => [
            [
                'coverage-xml' => 'coverage.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-xml=coverage.xml',
            ]
        ];
        yield 'testsuite' => [
            [
                'testsuite' => 'testsuite',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--testsuite=testsuite',
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--verbose=1',
            ]
        ];
        yield 'group' => [
            [
                'group' => ['group1', 'group2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--group=group1,group2',
            ]
        ];
    }
}
