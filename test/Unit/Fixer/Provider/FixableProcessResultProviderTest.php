<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Fixer\Provider;

use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Runner\TaskResultInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\Process;

class FixableProcessResultProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_can_build_a_fixable_task_result(): void
    {
        $process = $this->mockProcess($command = 'command');
        $taskResult = $this->mockTaskResult();

        $result = FixableProcessResultProvider::provide($taskResult, static function () use ($process) {
            return $process;
        });

        self::assertNotSame($taskResult, $result);
        self::assertInstanceOf(FixableTaskResult::class, $result);
        self::assertSame(
            PHP_EOL . PHP_EOL . 'You can fix errors by running the following command:' . PHP_EOL . $command,
            $result->getMessage()
        );
    }


    private function mockTaskResult(): TaskResultInterface
    {
        /** @var TaskResultInterface&ObjectProphecy $taskResult */
        $taskResult = $this->prophesize(TaskResultInterface::class);
        $taskResult->withAppendedMessage(Argument::type('string'))->will(
            function (array $arguments) use ($taskResult) {
                $taskResult->getMessage()->willReturn($arguments[0]);
                return $taskResult;
            }
        );

        return $taskResult->reveal();
    }

    private function mockProcess(string $command): Process
    {
        /** @var Process&ObjectProphecy $taskResult */
        $process = $this->prophesize(Process::class);
        $process->getCommandLine()->willReturn($command);

        return $process->reveal();
    }
}
