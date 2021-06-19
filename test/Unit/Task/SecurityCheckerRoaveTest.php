<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityCheckerRoave;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use GrumPHP\Util\Filesystem;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

class SecurityCheckerRoaveTest extends AbstractExternalTaskTestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $filesystem;

    protected function provideTask(): TaskInterface
    {
        $this->filesystem = $this->prophesize(Filesystem::class);

        return new SecurityCheckerRoave(
            $this->processBuilder->reveal(),
            $this->formatter->reveal(),
            $this->filesystem->reveal()
        );
    }

    private function mockComposerJsonWithRoaveSecurityAdvisories(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(true);
        $this->filesystem->readPath(Argument::exact('./composer.json'))->willReturn(
            json_encode([
                'require' => ['roave/security-advisories'=>'dev-latest'],
            ])
        );
    }

    private function mockComposerJsonWithoutRoaveSecurityAdvisories(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(true);
        $this->filesystem->readPath(Argument::exact('./composer.json'))->willReturn(
            json_encode([
                'require' => [],
            ])
        );
    }

    private function mockMissingComposerJson(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(false);
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'jsonfile' => './composer.json',
                'lockfile' => './composer.lock',
                'run_always' => false,
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
            $this->mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', $process = $this->mockProcess(1));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'no-roave-security-advisories' =>
        [
            [],
            $this->mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->mockComposerJsonWithoutRoaveSecurityAdvisories();
            },
            'This task is only available when roave/security-advisories is installed as a library.'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            },
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            $this->mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            }
        ];
        yield 'no-composer.json-file' => [
            [
                'run_always' => true
            ],
            $this->mockContext(RunContext::class, []),
            function () {
                $this->mockMissingComposerJson();
            }
        ];
    }

    /**
     * @test
     * @dataProvider provideExternalTaskRuns
     */
    public function it_runs_external_task(
        array $config,
        ContextInterface $context,
        string $taskName,
        array $cliArguments,
        ?Process $process = null
    ): void
    {
        $configurator = function () {
            $this->mockComposerJsonWithRoaveSecurityAdvisories();
        };
        \Closure::bind($configurator, $this)();

        parent::it_runs_external_task($config,$context,$taskName,$cliArguments,$process);
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'update',
                '--dry-run',
                'roave/security-advisories',
            ]
        ];
    }
}
