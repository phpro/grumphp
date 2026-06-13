<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\MagoAnalyzer;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MagoAnalyzerTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new MagoAnalyzer(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'no-stubs' => null,
                'retain-codes' => [],
                'ignore-baseline' => null,
                'sort' => null,
                'fix-mode' => 'safe',
                'minimum-report-level' => null,
            ]
        ];

        yield 'invalid-fix-mode' => [['fix-mode' => 'invalid'], null];
        yield 'invalid-minimum-report-level' => [['minimum-report-level' => 'invalid'], null];
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
            self::mockContext(RunContext::class),
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

    #[Test]
    #[DataProvider('provideSkipsOnStuff')]
    public function it_skips_on_stuff(
        array            $config,
        ContextInterface $context,
        callable         $configurator
    ): void
    {
        self::markTestSkipped('No skip scenarios defined yet');
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-skip-scenarios' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze']
        ];

        yield 'pre-commit-staged' => [
            [],
            self::mockContext(GitPreCommitContext::class),
            'mago',
            ['analyze', '--staged']
        ];

        yield 'no-stubs' => [
            ['no-stubs' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze', '--no-stubs']
        ];

        yield 'retain-codes' => [
            ['retain-codes' => ['invalid-argument', 'semantics']],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze', '--retain-code', 'invalid-argument', '--retain-code', 'semantics']
        ];

        yield 'ignore-baseline' => [
            ['ignore-baseline' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze', '--ignore-baseline']
        ];

        yield 'sort' => [
            ['sort' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze', '--sort']
        ];

        yield 'minimum-report-level' => [
            ['minimum-report-level' => 'warning'],
            self::mockContext(RunContext::class),
            'mago',
            ['analyze', '--minimum-report-level', 'warning']
        ];
    }
}
