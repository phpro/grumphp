<?php

declare(strict_types=1);

namespace GrumPHP\Test\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

abstract class AbstractExternalTaskTestCase extends AbstractTaskTestCase
{
    /**
     * @var ProcessBuilder|ObjectProphecy
     */
    protected $processBuilder;

    /**
     * @var ProcessFormatterInterface|ObjectProphecy
     */
    protected $formatter;

    abstract public function provideExternalTaskRuns(): iterable;

    protected function setUp(): void
    {
        $this->processBuilder = $this->prophesize(ProcessBuilder::class);
        $this->formatter = $this->prophesize(ProcessFormatterInterface::class);
        parent::setUp();
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
        $task = $this->configureTask($config);
        $this->processBuilder->createArgumentsForCommand($taskName)->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );

        // Added extra boilerplate since ->will() overwrites the bound $this context.
        $cliArgumentResolver = function (array $cliArguments, array $processArguments): array {
            return $this->resolveExpectedCliArgumentFromCallable($cliArguments, $processArguments);
        };

        $process = $process ?? $this->mockProcess(0);
        $this->processBuilder->buildProcess(Argument::any())
            ->shouldBeCalled()
            ->will(function ($parameters) use ($cliArguments, $process, $cliArgumentResolver) {
                $processArguments = $parameters[0]->getValues();
                $resolvedCliArguments = $cliArgumentResolver($cliArguments, $processArguments);

                Assert::assertSame($resolvedCliArguments, $processArguments);
                return $process;
            });

        $result = $task->run($context);
        self::assertInstanceOf(TaskResultInterface::class, $result);
    }

    /**
     * This function makes it possible to create a expected argument callback that takes the actual argument as input.
     * This can be handy for validating a part of the argument.
     */
    private function resolveExpectedCliArgumentFromCallable(array $expectedArguments, array $actualArguments): array
    {
        self::assertSameSize(
            $expectedArguments,
            $actualArguments,
            'Received following arguments on CLI:'.implode(',', $actualArguments)
        );

        return array_map(
            function ($expected, $actual) {
                if ($expected instanceof \Closure) {
                    return $expected($actual);
                }

                return $expected;
            },
            $expectedArguments,
            $actualArguments
        );
    }

    protected function mockProcess(int $exitCode = 0, string $output = '', string $errors = ''): Process
    {
        /** @var Process|ObjectProphecy $process */
        $process = $this->prophesize(Process::class);
        $process->run()->willReturn($exitCode);
        $process->getExitCode()->willReturn($exitCode);
        $process->isSuccessful()->willReturn($exitCode === 0);
        $process->getOutput()->willReturn($output);
        $process->getErrorOutput()->willReturn($errors);
        $process->getCommandLine()->willReturn('mocked-cli');

        $process->setWorkingDirectory(Argument::any())->will(function ($arguments) {
            $this->getWorkingDirectory()->willReturn($arguments[0]);
        });

        return $process->reveal();
    }

    protected function mockProcessBuilder(string $taskName, Process $process)
    {
        $this->processBuilder->createArgumentsForCommand($taskName)->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );
        $this->processBuilder->buildProcess($arguments)->willReturn($process);
    }

    protected function mockProcessWithStdIn(int $exitCode = 0, string $output = '', string $errors = '') {
        /** @var Process|ObjectProphecy $process */
        $process = $this->prophesize(Process::class);
        $process->setInput(Argument::type(InputStream::class))->shouldBeCalled();
        $process->start()->shouldBeCalled();
        $process->wait()->shouldBeCalled()->willReturn($exitCode);
        $process->isSuccessful()->willReturn($exitCode === 0);
        $process->getOutput()->willReturn($output);
        $process->getErrorOutput()->willReturn($errors);

        return $process->reveal();
    }
}
