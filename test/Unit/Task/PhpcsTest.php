<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\PhpcsFormatter;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpcs;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class PhpcsTest extends AbstractExternalTaskTestCase
{
    /**
     * @var PhpcsFormatter|ObjectProphecy
     */
    protected $formatter;

    protected function provideTask(): TaskInterface
    {
        $this->formatter = $this->prophesize(PhpcsFormatter::class);
        return new Phpcs(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'standard' => [],
                'tab_width' => null,
                'encoding' => null,
                'whitelist_patterns' => [],
                'ignore_patterns' => [],
                'sniffs' => [],
                'severity' => null,
                'error_severity' => null,
                'warning_severity' => null,
                'triggered_by' => ['php'],
                'report' => 'full',
                'report_width' => null,
                'exclude' => [],
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
        yield 'exitCode1WithoutFixer' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcs', $process = $this->mockProcess(1));
                $this->processBuilder->createArgumentsForCommand('phpcbf')->willThrow(CommandNotFoundException::class);
                $this->formatter->format($process)->will(function () {
                    $this->getSuggestedFiles()->willReturn(['hello.php']);
                    return 'nope';
                });
            },
            'nope'.PHP_EOL.'Info: phpcbf could not be found. Please consider to install it for auto-fixing'
        ];
        yield 'exitCode1WithoutFixerBecauseOfNoFiles' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcs', $process = $this->mockProcess(1));
                $this->processBuilder->createArgumentsForCommand('phpcbf')->shouldNotBeCalled();
                $this->formatter->format($process)->will(function () {
                    $this->getSuggestedFiles()->willReturn([]);
                    return 'nope';
                });
            },
            'nope'
        ];
        yield 'exitCode1WithFixer' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcs', $process = $this->mockProcess(1));
                $this->processBuilder->createArgumentsForCommand('phpcbf')->willReturn(
                    $fixerArguments = new ProcessArgumentsCollection(['phpcbf'])
                );
                $this->processBuilder->buildProcess($fixerArguments)->willReturn($phpcbdProcess = $this->mockProcess(0));

                $this->formatter->format($process)->will(function (): string {
                    $this->getSuggestedFiles()->willReturn(['hello.php']);
                    return 'nope';
                });
            },
            'nope',
            FixableTaskResult::class
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcs', $this->mockProcess(0));
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
        yield 'no-files-after-whitelist' => [
            [
                'whitelist_patterns' => ['src/'],
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
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'standard' => [
            [
                'standard' => ['PSR1', 'PSR2']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--standard=PSR1,PSR2',
                '--extensions=php',
                '--report=full',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'extensions' => [
            [
                'triggered_by' => ['php', 'phtml']
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php,phtml',
                '--report=full',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'tab-width' => [
            [
                'tab_width' => 4,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--tab-width=4',
                '--report=full',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'encoding' => [
            [
                'encoding' => 'UTF-8',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--encoding=UTF-8',
                '--report=full',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'report' => [
            [
                'report' => 'small',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=small',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'report-width' => [
            [
                'report_width' => 20,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--report-width=20',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'severity' => [
            [
                'severity' => 5,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--severity=5',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'error-severity' => [
            [
                'error_severity' => 5,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--error-severity=5',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'warning-severity' => [
            [
                'warning_severity' => 5,
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--warning-severity=5',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'sniffs' => [
            [
                'sniffs' => ['sniff1', 'sniff2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--sniffs=sniff1,sniff2',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'ignore-patternes' => [
            [
                'ignore_patterns' => ['ignore1', 'ignore2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--ignore=ignore1,ignore2',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
        yield 'exclude' => [
            [
                'exclude' => ['exclude1', 'exclude2'],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcs',
            [
                '--extensions=php',
                '--report=full',
                '--exclude=exclude1,exclude2',
                '--report-json',
                $this->expectFileList('hello.php'.PHP_EOL.'hello2.php'),
            ]
        ];
    }

    private function expectFileList(string $expectedContents): callable
    {
        return static function (string $argument) use ($expectedContents) {
            self::assertStringStartsWith('--file-list=', $argument);
            list($arg, $tmpFile) = explode('=', $argument, 2);

            self::assertFileExists($tmpFile);
            self::assertStringEqualsFile($tmpFile, 'hello.php'.PHP_EOL.'hello2.php');

            return $argument;
        };
    }
}
