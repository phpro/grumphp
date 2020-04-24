<?php

declare(strict_types=1);

namespace GrumPHP\Test\Runner;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

abstract class AbstractTaskHandlerMiddlewareTestCase extends AbstractMiddlewareTestCase
{
    protected function createNextResultCallback(TaskResultInterface $taskResult): callable
    {
        return static function () use ($taskResult) {
            return new Success($taskResult);
        };
    }

    protected function createExceptionCallback(\Throwable $exception): callable
    {
        return static function () use ($exception) {
            return new Failure($exception);
        };
    }

    protected function resolve(Promise $promise): TaskResultInterface
    {
        return wait($promise);
    }

    protected function mockTaskRun(string $name, callable $runWillDo): TaskInterface
    {
        /** @var ObjectProphecy|TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig($name, [], new Metadata([])));
        $task->run(Argument::type(ContextInterface::class))->will($runWillDo);

        return $task->reveal();
    }
}
