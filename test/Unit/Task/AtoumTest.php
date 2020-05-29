<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Atoum;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class AtoumTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Atoum(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config_file' => null,
                'bootstrap_file' => null,
                'directories' => [],
                'files' => [],
                'namespaces' => [],
                'methods' => [],
                'tags' => [],
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
                $this->mockProcessBuilder('atoum', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('atoum', $this->mockProcess(0));
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
            'atoum',
            []
        ];
        yield 'config' => [
            [
                'config_file' => 'configfile'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '-c',
                'configfile',
            ]
        ];
        yield 'bootstrap-file' => [
            [
                'bootstrap_file' => 'bootstrapfile'
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--bootstrap-file',
                'bootstrapfile',
            ]
        ];
        yield 'directories' => [
            [
                'directories' => ['src', 'tst']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--directories',
                'src',
                'tst',
            ]
        ];
        yield 'files' => [
            [
                'files' => ['hello.php', 'hello2.php']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--files',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'namespaces' => [
            [
                'namespaces' => ['ns1', 'ns2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--namespaces',
                'ns1',
                'ns2',
            ]
        ];
        yield 'methods' => [
            [
                'methods' => ['method1', 'method2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--methods',
                'method1',
                'method2',
            ]
        ];
        yield 'tags' => [
            [
                'tags' => ['tag1', 'tag2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--tags',
                'tag1',
                'tag2',
            ]
        ];
    }
}
