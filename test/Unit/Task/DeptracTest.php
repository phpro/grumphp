<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Deptrac;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class DeptracTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Deptrac(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'depfile' => null,
                'formatter' => null,
                'output' => null,
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
                $this->mockProcessBuilder('deptrac', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('deptrac', $this->mockProcess(0));
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
            'deptrac',
            [
                'analyse',
            ]
        ];
        yield 'formatter-graphviz' => [
            [
                'formatter' => 'graphviz-display',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-display',
            ]
        ];
        yield 'formatter-graphviz-dump-image' => [
            [
                'formatter' => 'graphviz-image',
                'output' => 'file.jpg',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-image',
                '--output=file.jpg',
            ]
        ];
        yield 'formatter-graphviz-dump-dot' => [
            [
                'formatter' => 'graphviz-dot',
                'output' => 'file.dot',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-dot',
                '--output=file.dot',
            ]
        ];
        yield 'formatter-graphviz-dump-html' => [
            [
                'formatter' => 'graphviz-html',
                'output' => 'file.html',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-html',
                '--output=file.html',
            ]
        ];
        yield 'depfile' => [
            [
                'depfile' => 'depfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--config-file=depfile',
            ]
        ];
        yield 'formatter-junit' => [
            [
                'formatter' => 'junit',
                'output' => 'junit.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=junit',
                '--output=junit.xml',
            ]
        ];
        yield 'formatter-xml' => [
            [
                'formatter' => 'xml',
                'output' => 'file.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=xml',
                '--output=file.xml',
            ]
        ];
        yield 'formatter-baseline' => [
            [
                'formatter' => 'baseline',
                'output' => 'baseline.yaml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=baseline',
                '--output=baseline.yaml',
            ]
        ];
    }
}
