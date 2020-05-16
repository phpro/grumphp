<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\DoctrineOrm;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class DoctrineOrmTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new DoctrineOrm(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'skip_mapping' => false,
                'skip_sync' => false,
                'triggered_by' => ['php', 'xml', 'yml'],
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
                $this->mockProcessBuilder('doctrine', $process = $this->mockProcess(1));
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
                $this->mockProcessBuilder('doctrine', $this->mockProcess(0));
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
            'doctrine',
            [
                'orm:validate-schema',
            ]
        ];
        yield 'skip-mapping' => [
            [
                'skip_mapping' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'doctrine',
            [
                'orm:validate-schema',
                '--skip-mapping',
            ]
        ];
        yield 'skip-sync' => [
            [
                'skip_sync' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'doctrine',
            [
                'orm:validate-schema',
                '--skip-sync',
            ]
        ];
    }
}
