<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\MagoLinter;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MagoLinterTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new MagoLinter(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'semantics' => null,
                'pedantic' => null,
                'only' => [],
                'staged' => null,
                'retain-codes' => [],
                'ignore-baseline' => null,
                'fix' => null,
                'fail-on-remaining' => null,
                'sort' => null,
                'fixable-only' => null,
                'reporting-format' => null,
                'reporting-target' => null,
                'minimum-report-level' => null,
                'minimum-fail-level' => null,
                'dry-run' => null,
            ]
        ];

        yield 'invalid-fix' => [['fix' => 'invalid'], null];
        yield 'invalid-reporting-format' => [['reporting-format' => 'invalid'], null];
        yield 'invalid-reporting-target' => [['reporting-target' => 'invalid'], null];
        yield 'invalid-minimum-report-level' => [['minimum-report-level' => 'invalid'], null];
        yield 'invalid-minimum-fail-level' => [['minimum-fail-level' => 'invalid'], null];
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
        ];

        yield 'fail-on-remaining-without-fix' => [
            ['fail-on-remaining' => true],
            self::mockContext(RunContext::class),
            function () {},
            'Fail on remaining option is only supported with fix option.',
        ];

        yield 'dry-run-without-fix' => [
            ['dry-run' => true],
            self::mockContext(RunContext::class),
            function () {},
            'Dry run option is only supported with fix option.',
        ];

        yield 'fixable-only-with-fix' => [
            ['fix' => 'safe', 'fixable-only' => true],
            self::mockContext(RunContext::class),
            function () {},
            'Fixable-only option is not supported with fix option.',
        ];

        yield 'reporting-format-with-fix' => [
            ['fix' => 'safe', 'reporting-format' => 'json'],
            self::mockContext(RunContext::class),
            function () {},
            'Reporting format option is not supported with fix option.',
        ];

        yield 'reporting-target-with-fix' => [
            ['fix' => 'safe', 'reporting-target' => 'stderr'],
            self::mockContext(RunContext::class),
            function () {},
            'Reporting target option is not supported with fix option.',
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
            function () {
            }
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class),
            'mago',
            ['lint']
        ];

        yield 'fix-safe' => [
            ['fix' => 'safe'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fix']
        ];

        yield 'fix-potentially-unsafe' => [
            ['fix' => 'potentially-unsafe'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fix', '--potentially-unsafe']
        ];

        yield 'fix-unsafe' => [
            ['fix' => 'unsafe'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fix', '--unsafe']
        ];

        yield 'fix-with-fail-on-remaining' => [
            ['fix' => 'safe', 'fail-on-remaining' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fix', '--fail-on-remaining']
        ];

        yield 'fix-with-dry-run' => [
            ['fix' => 'safe', 'dry-run' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fix', '--dry-run']
        ];

        yield 'fixable-only' => [
            ['fixable-only' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--fixable-only']
        ];

        yield 'reporting-format' => [
            ['reporting-format' => 'json'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--reporting-format', 'json']
        ];

        yield 'reporting-target' => [
            ['reporting-target' => 'stderr'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--reporting-target', 'stderr']
        ];

        yield 'only' => [
            ['only' => ['invalid-argument', 'semantics']],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--only=invalid-argument,semantics']
        ];

        yield 'retain-codes' => [
            ['retain-codes' => ['invalid-argument', 'semantics']],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--retain-code', 'invalid-argument', '--retain-code', 'semantics']
        ];

        yield 'minimum-report-level' => [
            ['minimum-report-level' => 'warning'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--minimum-report-level', 'warning']
        ];

        yield 'minimum-fail-level' => [
            ['minimum-fail-level' => 'error'],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--minimum-fail-level', 'error']
        ];

        yield 'semantics' => [
            ['semantics' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--semantics']
        ];

        yield 'pedantic' => [
            ['pedantic' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--pedantic']
        ];

        yield 'ignore-baseline' => [
            ['ignore-baseline' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--ignore-baseline']
        ];

        yield 'sort' => [
            ['sort' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--sort']
        ];

        yield 'staged' => [
            ['staged' => true],
            self::mockContext(RunContext::class),
            'mago',
            ['lint', '--staged']
        ];
    }
}
