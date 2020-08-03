<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Infection;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class InfectionTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Infection(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'threads' => null,
                'test_framework' => null,
                'only_covered' => false,
                'configuration' => null,
                'min_msi' => null,
                'min_covered_msi' => null,
                'mutators' => [],
                'ignore_patterns' => [],
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
                $this->mockProcessBuilder('infection', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('infection', $this->mockProcess(0));
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
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
            ]
        ];
        yield 'threads' => [
            [
                'threads' => 5,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--threads=5'
            ]
        ];
        yield 'test-framework' => [
            [
                'test_framework' => 'phpunit',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--test-framework=phpunit'
            ]
        ];
        yield 'only-covered' => [
            [
                'only_covered' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--only-covered'
            ]
        ];
        yield 'configuration' => [
            [
                'configuration' => 'file',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--configuration=file'
            ]
        ];
        yield 'min-msi' => [
            [
                'min_msi' => 100,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--min-msi=100'
            ]
        ];
        yield 'min-covered-msi' => [
            [
                'min_covered_msi' => 100,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--min-covered-msi=100'
            ]
        ];
        yield 'mutators' => [
            [
                'mutators' => ['A', 'B', 'C'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--mutators=A,B,C'
            ]
        ];
        yield 'with_filtered_files' => [
            [
            ],
            $this->mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--filter=hello.php,hello2.php'
            ]
        ];
    }
}
