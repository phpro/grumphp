<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Runner\Middleware\TasksFilteringRunnerMiddleware;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use GrumPHP\TestSuite\TestSuite;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TasksFilteringRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var TasksFilteringRunnerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->middleware = new TasksFilteringRunnerMiddleware();
    }

    /** @test */
    public function it_filters_tasks_by_all_possible_filters(): void
    {
        $context = (new TaskRunnerContext(
            new RunContext(new FilesCollection()),
            new TestSuite('suite', ['task1', 'task2']),
            ['task1']
        ))->withTasks(new TasksCollection([
            $task1 = $this->mockTaskWithContext('task1', RunContext::class),
            $task2 = $this->mockTaskWithContext('task2', RunContext::class),
            $task3 = $this->mockTaskWithContext('task3', ContextInterface::class),
            $task3 = $this->mockTaskWithContext('task4', RunContext::class),
        ]));

        $next = function (TaskRunnerContext $passedContext) use ($context, $task1) {
            self::assertSame($context->getTaskContext(), $passedContext->getTaskContext());
            self::assertSame($context->getTestSuite(), $passedContext->getTestSuite());
            self::assertSame($context->getTaskNames(), $passedContext->getTaskNames());
            self::assertSame([$task1], $passedContext->getTasks()->getValues());

            return new TaskResultCollection();
        };

        $results = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $results);
    }

    /** @test */
    public function it_filters_tasks_by_context(): void
    {
        $context = (new TaskRunnerContext(
            new RunContext(new FilesCollection()),
            null,
            []
        ))->withTasks(new TasksCollection([
            $task1 = $this->mockTaskWithContext('task1', RunContext::class),
            $task3 = $this->mockTaskWithContext('task3', ContextInterface::class),
        ]));

        $next = function (TaskRunnerContext $passedContext) use ($context, $task1) {
            self::assertSame($context->getTaskContext(), $passedContext->getTaskContext());
            self::assertSame($context->getTestSuite(), $passedContext->getTestSuite());
            self::assertSame($context->getTaskNames(), $passedContext->getTaskNames());
            self::assertSame([$task1], $passedContext->getTasks()->getValues());

            return new TaskResultCollection();
        };

        $results = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $results);
    }

    /** @test */
    public function it_filters_tasks_by_testsuite(): void
    {
        $context = (new TaskRunnerContext(
            new RunContext(new FilesCollection()),
            new TestSuite('suite', ['task1', 'task2']),
            []
        ))->withTasks(new TasksCollection([
            $task1 = $this->mockTaskWithContext('task1', RunContext::class),
            $task2 = $this->mockTaskWithContext('task2', RunContext::class),
            $task3 = $this->mockTaskWithContext('task4', RunContext::class),
        ]));

        $next = function (TaskRunnerContext $passedContext) use ($context, $task1, $task2) {
            self::assertSame($context->getTaskContext(), $passedContext->getTaskContext());
            self::assertSame($context->getTestSuite(), $passedContext->getTestSuite());
            self::assertSame($context->getTaskNames(), $passedContext->getTaskNames());
            self::assertSame([$task1, $task2], $passedContext->getTasks()->getValues());

            return new TaskResultCollection();
        };

        $results = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $results);
    }

    /** @test */
    public function it_filters_tasks_by_tasknames(): void
    {
        $context = (new TaskRunnerContext(
            new RunContext(new FilesCollection()),
            null,
            ['task1']
        ))->withTasks(new TasksCollection([
            $task1 = $this->mockTaskWithContext('task1', RunContext::class),
            $task2 = $this->mockTaskWithContext('task2', RunContext::class),
        ]));

        $next = function (TaskRunnerContext $passedContext) use ($context, $task1) {
            self::assertSame($context->getTaskContext(), $passedContext->getTaskContext());
            self::assertSame($context->getTestSuite(), $passedContext->getTestSuite());
            self::assertSame($context->getTaskNames(), $passedContext->getTaskNames());
            self::assertSame([$task1], $passedContext->getTasks()->getValues());

            return new TaskResultCollection();
        };

        $results = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $results);
    }

    private function mockTaskWithContext(string $name, string $contextClass): TaskInterface
    {
        /** @var ObjectProphecy|TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig($name, [], new Metadata([])));
        $task->canRunInContext(
            Argument::type(ContextInterface::class))->will(function (array $arguments) use ($contextClass) : bool {
                return get_class($arguments[0]) === $contextClass;
            }
        );

        return $task->reveal();
    }
}
