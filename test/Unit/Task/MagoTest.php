<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Mago;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;

class MagoTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Mago(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'formatter' => true,
                'formatter_options' => ['--staged'],
                'linter' => true,
                'linter_options' => ['--staged'],
                'analyzer' => true,
                'analyzer_options' => ['--staged'],
                'guard' => false,
                'guard_options' => [],
            ]
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            self::mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            self::mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            self::mockContext()
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('mago', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('mago', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-commands' => [
            [
                'formatter' => false,
                'linter' => false,
                'analyzer' => false,
                'guard' => false,
            ],
            self::mockContext(RunContext::class, ['file.php']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'formatter' => [
            [
                'formatter' => true,
                'formatter_options' => ['--check'],
                'linter' => false,
                'linter_options' => [],
                'analyzer' => false,
                'analyzer_options' => [],
                'guard' => false,
                'guard_options' => [],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'mago',
            [
                'fmt',
                '--check',
            ]
        ];

        yield 'linter' => [
            [
                'formatter' => false,
                'formatter_options' => [],
                'linter' => true,
                'linter_options' => ['--semantics'],
                'analyzer' => false,
                'analyzer_options' => [],
                'guard' => false,
                'guard_options' => [],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'mago',
            [
                'lint',
                '--semantics',
            ]
        ];

        yield 'analyzer' => [
            [
                'formatter' => false,
                'formatter_options' => [],
                'linter' => false,
                'linter_options' => [],
                'analyzer' => true,
                'analyzer_options' => ['--no-stubs'],
                'guard' => false,
                'guard_options' => [],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'mago',
            [
                'analyze',
                '--no-stubs',
            ]
        ];

        yield 'architectural-guard' => [
            [
                'formatter' => false,
                'formatter_options' => [],
                'linter' => false,
                'linter_options' => [],
                'analyzer' => false,
                'analyzer_options' => [],
                'guard' => true,
                'guard_options' => ['--structural'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'mago',
            [
                'guard',
                '--structural',
            ]
        ];
    }

    #[DataProvider('provideMultipleExternalTaskRuns')]
    #[Test]
    public function it_runs_multiple_external_commands(
        array $config,
        ContextInterface $context,
        string $taskName,
        array $expectedCommands
    ): void {
        $task = $this->configureTask($config);

        $this->processBuilder->createArgumentsForCommand($taskName)->will(function () {
            return new \GrumPHP\Collection\ProcessArgumentsCollection();
        });

        $count = 0;
        $this->processBuilder->buildProcess(Argument::any())
            ->shouldBeCalledTimes(count($expectedCommands))
            ->will(function ($parameters) use ($expectedCommands, &$count) {
                $cliArguments = $expectedCommands[$count++];
                $processArguments = $parameters[0]->getValues();
                \PHPUnit\Framework\Assert::assertSame($cliArguments, $processArguments);

                return MagoTest::mockProcess(0);
            });

        $result = $task->run($context);
        self::assertInstanceOf(\GrumPHP\Runner\TaskResultInterface::class, $result);
        self::assertTrue($result->isPassed());
    }

    public static function provideMultipleExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            'mago',
            [
                ['fmt', '--staged'],
                ['lint', '--staged'],
                ['analyze', '--staged'],
            ]
        ];

        yield 'formatter-and-linter' => [
            [
                'formatter' => true,
                'formatter_options' => ['--staged'],
                'linter' => true,
                'linter_options' => ['--staged'],
                'analyzer' => false,
                'analyzer_options' => [],
                'guard' => false,
                'guard_options' => [],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            'mago',
            [
                ['fmt', '--staged'],
                ['lint', '--staged'],
            ]
        ];

        yield 'all-commands' => [
            [
                'formatter' => true,
                'formatter_options' => ['--staged'],
                'linter' => true,
                'linter_options' => ['--staged', '--semantics', '--minimum-report-level=warning'],
                'analyzer' => true,
                'analyzer_options' => ['--staged'],
                'guard' => true,
                'guard_options' => ['--structural'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            'mago',
            [
                [
                    'fmt',
                    '--staged',
                ],
                [
                    'lint',
                    '--staged',
                    '--semantics',
                    '--minimum-report-level=warning',
                ],
                [
                    'analyze',
                    '--staged',
                ],
                [
                    'guard',
                    '--structural',
                ],
            ]
        ];
    }
}
