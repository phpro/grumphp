<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\Middleware\GroupByPriorityMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class GroupByPriorityMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var GroupByPriorityMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->middleware = new GroupByPriorityMiddleware(
            $this->mockIO(),
            new RunnerConfig($stopOnFailure = false, $hideCircumventionTip = false, $additionalInfo = '')
        );
    }

    /** @test */
    public function it_run_tasks_grouped_by_priority(): void
    {
        $context = $this->createRunnerContext()->withTasks(new TasksCollection([
            $task1 = $this->mockTask('task1', ['priority' => 100]),
            $task2 = $this->mockTask('task2', ['priority' => 100]),
            $task3 = $this->mockTask('task3', ['priority' => 200]),
        ]));

        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection(
                $passedContext->getTasks()->map(function (TaskInterface $task) use ($passedContext) {
                    return TaskResult::createFailed($task, $passedContext->getTaskContext(), 'ERROR');
                })->toArray()
            );
        };

        $result = $this->middleware->handle($context, $next);
        self::assertCount(3, $result);
        self::assertSame($result->get(0)->getTask(), $task3);
        self::assertSame($result->get(1)->getTask(), $task1);
        self::assertSame($result->get(2)->getTask(), $task2);
    }

    /** @test */
    public function it_can_skip_on_failure(): void
    {
        $this->middleware = new GroupByPriorityMiddleware(
            $this->mockIO(),
            new RunnerConfig($stopOnFailure = true, $hideCircumventionTip = false, $additionalInfo = '')
        );

        $context = $this->createRunnerContext()->withTasks(new TasksCollection([
            $task1 = $this->mockTask('task1', ['priority' => 100]),
            $task2 = $this->mockTask('task2', ['priority' => 100]),
            $task3 = $this->mockTask('task3', ['priority' => 200]),
        ]));

        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection(
                $passedContext->getTasks()->map(function (TaskInterface $task) use ($passedContext) {
                    return TaskResult::createFailed($task, $passedContext->getTaskContext(), 'ERROR');
                })->toArray()
            );
        };

        $result = $this->middleware->handle($context, $next);
        self::assertCount(1, $result);
        self::assertSame($result->get(0)->getTask(), $task3);
    }
}
