<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\MagoFormatter;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MagoFormatterTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new MagoFormatter(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'unknown-option' => [
            ['unknown' => true],
            null
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
        yield 'exitCode1-run' => [
            [],
            self::mockContext(RunContext::class),
            function () {
                $this->mockProcessBuilder('mago', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class,
        ];

        yield 'exitCode1-pre-commit' => [
            [],
            self::mockContext(GitPreCommitContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('mago', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class,
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class),
            function () {
                $this->mockProcessBuilder('mago', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'pre-commit-without-php-files' => [
            [],
            self::mockContext(GitPreCommitContext::class, ['notes.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class),
            'mago',
            ['format', '--dry-run']
        ];

        yield 'pre-commit-staged-files' => [
            [],
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'mago',
            ['format', '--dry-run', 'hello.php', 'hello2.php']
        ];
    }

    /**
     * On failure GrumPHP offers a fix command that re-runs without --dry-run (applying the
     * formatting in-place). The harness only asserts the detection command, so the fix command is
     * verified here by inspecting the mutated arguments collection after the run.
     */
    #[Test]
    #[DataProvider('provideFixCommands')]
    public function it_builds_the_fix_command(ContextInterface $context, array $expectedFixCommand): void
    {
        $this->processBuilder->createArgumentsForCommand('mago')->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );
        $this->processBuilder->buildProcess($arguments)->willReturn($process = self::mockProcess(1));
        $this->formatter->format($process)->willReturn('failed');

        $this->configureTask([])->run($context);

        self::assertSame($expectedFixCommand, $arguments->getValues());
    }

    public static function provideFixCommands(): iterable
    {
        yield 'run' => [
            self::mockContext(RunContext::class),
            ['format']
        ];

        yield 'pre-commit' => [
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            ['format', 'hello.php', 'hello2.php']
        ];
    }
}
