<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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

    protected function setUp()
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
        array $cliArguments
    ): void
    {
        $task = $this->configureTask($config);
        $this->processBuilder->createArgumentsForCommand($taskName)->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );

        $process = $this->mockProcess(0);
        $this->processBuilder->buildProcess(Argument::any())
            ->shouldBeCalled()
            ->will(function ($parameters) use ($cliArguments, $process) {
                Assert::assertSame($cliArguments, $parameters[0]->getValues());
                return $process;
            });

        $result = $task->run($context);
        self::assertInstanceOf(TaskResultInterface::class, $result);
    }

    protected function mockProcess(int $exitCode = 0, string $output = '', string $errors = ''): Process
    {
        /** @var Process|ObjectProphecy $process */
        $process = $this->prophesize(Process::class);
        $process->run()->willReturn($exitCode);
        $process->isSuccessful()->willReturn($exitCode === 0);
        $process->getOutput()->willReturn($output);
        $process->getErrorOutput()->willReturn($errors);

        return $process->reveal();
    }

    protected function mockProcessBuilder(string $taskName, Process $process)
    {
        $this->processBuilder->createArgumentsForCommand($taskName)->willReturn(
            $arguments = new ProcessArgumentsCollection()
        );
        $this->processBuilder->buildProcess($arguments)->willReturn($process);
    }
}
