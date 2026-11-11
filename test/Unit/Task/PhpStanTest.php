<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpStan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PhpStanTest extends AbstractExternalTaskTestCase
{
    private const FIXABLE_OUTPUT = <<<'TXT'
 [ERROR] Found 2 errors

🔧 1 error can be fixed automatically. Run PHPStan again with --fix.
TXT;

    private const FIXABLE_IGNORED_OUTPUT = <<<'TXT'
 [ERROR] Found 1 error

🔧 1 ignored error can be fixed automatically. Run PHPStan again with --fix-ignored.
TXT;

    protected function provideTask(): TaskInterface
    {
        return new PhpStan(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'autoload_file' => null,
                'configuration' => null,
                'level' => null,
                'ignore_patterns' => [],
                'force_patterns' => [],
                'triggered_by' => ['php'],
                'memory_limit' => null,
                'use_grumphp_paths' => true,
            ],
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
                $this->mockProcessBuilder('phpstan', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'exitCode1-with-fixable-errors' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpstan', $process = self::mockProcess(1, self::FIXABLE_OUTPUT));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class
        ];
        yield 'exitCode1-with-only-fixable-ignored-errors' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpstan', $process = self::mockProcess(1, self::FIXABLE_IGNORED_OUTPUT));
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
                $this->mockProcessBuilder('phpstan', self::mockProcess(0));
            }
        ];

        yield 'no-php-files-but-with-force-patterns' => [
            [
                'force_patterns' => ['file.txt'],
            ],
            self::mockContext(RunContext::class, ['file.txt']),
            function () {
                $this->mockProcessBuilder('phpstan', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function () {}
        ];
        yield 'no-files-with-use-grumphp-paths' => [
            [
                'use_grumphp_paths' => true,
            ],
            self::mockContext(RunContext::class),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpstan',
            [
                'analyse',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'autoload' => [
            [
                'autoload_file' => 'autoload.php'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpstan',
            [
                'analyse',
                '--autoload-file=autoload.php',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'configuration' => [
            [
                'configuration' => 'configurationfile'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpstan',
            [
                'analyse',
                '--configuration=configurationfile',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'memory-limit' => [
            [
                'memory_limit' => '250MB'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpstan',
            [
                'analyse',
                '--memory-limit=250MB',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'level' => [
            [
                'level' => 9001,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpstan',
            [
                'analyse',
                '--level=9001',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'no_files' => [
            [
                'use_grumphp_paths' => false,
            ],
            self::mockContext(RunContext::class, []),
            'phpstan',
            [
                'analyse',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
            ]
        ];
    }

    /**
     * When PHPStan reports auto-fixable errors, GrumPHP offers the same command with --fix added.
     * The harness only asserts the analyse command, so the fix command is verified here
     * by inspecting the mutated arguments collection.
     */
    #[Test]
    #[DataProvider('provideFixCommands')]
    public function it_builds_the_fix_command(
        array $config,
        string $output,
        string $errorOutput,
        array $expectedCommand
    ): void {
        $this->processBuilder->createArgumentsForCommand('phpstan')->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );
        $this->processBuilder->buildProcess($arguments)->willReturn(
            $process = self::mockProcess(1, $output, $errorOutput)
        );
        $this->formatter->format($process)->willReturn('failed');

        $this->configureTask($config)->run(self::mockContext(RunContext::class, ['hello.php']));

        self::assertSame($expectedCommand, $arguments->getValues());
    }

    public static function provideFixCommands(): iterable
    {
        yield 'fixable' => [
            [],
            self::FIXABLE_OUTPUT,
            '',
            ['analyse', '--no-ansi', '--no-interaction', '--no-progress', 'hello.php', '--fix'],
        ];
        yield 'fixable-keeps-options' => [
            ['configuration' => 'phpstan.neon', 'level' => 'max'],
            self::FIXABLE_OUTPUT,
            '',
            [
                'analyse',
                '--configuration=phpstan.neon',
                '--level=max',
                '--no-ansi',
                '--no-interaction',
                '--no-progress',
                'hello.php',
                '--fix',
            ],
        ];
        yield 'fixable-without-grumphp-paths' => [
            ['use_grumphp_paths' => false],
            self::FIXABLE_OUTPUT,
            '',
            ['analyse', '--no-ansi', '--no-interaction', '--no-progress', '--fix'],
        ];
        yield 'fixable-hint-on-error-output' => [
            [],
            '',
            '1 of the errors can be fixed automatically. Run PHPStan again with --fix to fix them first, then fix the rest.',
            ['analyse', '--no-ansi', '--no-interaction', '--no-progress', 'hello.php', '--fix'],
        ];
        yield 'not-fixable' => [
            [],
            ' [ERROR] Found 1 error',
            '',
            ['analyse', '--no-ansi', '--no-interaction', '--no-progress', 'hello.php'],
        ];
        yield 'only-fixable-ignored-errors' => [
            [],
            self::FIXABLE_IGNORED_OUTPUT,
            '',
            ['analyse', '--no-ansi', '--no-interaction', '--no-progress', 'hello.php'],
        ];
    }
}
