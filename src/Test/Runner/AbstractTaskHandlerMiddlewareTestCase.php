<?php

declare(strict_types=1);

namespace GrumPHP\Test\Runner;

use Amp\Future;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

abstract class AbstractTaskHandlerMiddlewareTestCase extends AbstractMiddlewareTestCase
{
    protected function createNextResultCallback(TaskResultInterface $taskResult): callable
    {
        return static function () use ($taskResult) {
            return Future::complete($taskResult);
        };
    }

    protected function createExceptionCallback(\Throwable $exception): callable
    {
        return static function () use ($exception) {
            return Future::error($exception);
        };
    }

    protected function resolve(Future $promise): TaskResultInterface
    {
        return $promise->await();
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
