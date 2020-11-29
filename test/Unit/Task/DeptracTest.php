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
                'formatter' => [],
                'graphviz_display' => true,
                'graphviz_dump_image' => null,
                'graphviz_dump_dot' => null,
                'graphviz_dump_html' => null,
                'junit_dump_xml' => null,
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
                'analyze',
                '--graphviz-display=1',
            ]
        ];
        yield 'formatter-graphviz' => [
            [
                'formatter' => ['graphviz'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=graphviz',
                '--graphviz-display=1',
            ]
        ];
        yield 'formatter-graphviz-no-display' => [
            [
                'formatter' => ['graphviz'],
                'graphviz_display' => false,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=graphviz',
                '--graphviz-display=0',
            ]
        ];
        yield 'formatter-graphviz-dump-image' => [
            [
                'formatter' => ['graphviz'],
                'graphviz_dump_image' => 'file.jpg',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=graphviz',
                '--graphviz-display=1',
                '--graphviz-dump-image=file.jpg',
            ]
        ];
        yield 'formatter-graphviz-dump-dot' => [
            [
                'formatter' => ['graphviz'],
                'graphviz_dump_dot' => 'file.dot',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=graphviz',
                '--graphviz-display=1',
                '--graphviz-dump-dot=file.dot',
            ]
        ];
        yield 'formatter-graphviz-dump-html' => [
            [
                'formatter' => ['graphviz'],
                'graphviz_dump_html' => 'file.html',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=graphviz',
                '--graphviz-display=1',
                '--graphviz-dump-html=file.html',
            ]
        ];
        yield 'depfile' => [
            [
                'depfile' => 'depfile',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--graphviz-display=1',
                'depfile',
            ]
        ];
        yield 'formatter-junit' => [
            [
                'formatter' => ['junit'],
                'junit_dump_xml' => 'junit.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyze',
                '--formatter=junit',
                '--graphviz-display=1',
                '--junit-dump-xml=junit.xml',
            ]
        ];
    }
}
