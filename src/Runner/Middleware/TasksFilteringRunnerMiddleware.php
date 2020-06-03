<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Runner\TaskRunnerContext;

class TasksFilteringRunnerMiddleware implements RunnerMiddlewareInterface
{
    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        return $next(
            $context->withTasks(
                (new TasksCollection($context->getTasks()->toArray()))
                    ->filterByContext($context->getTaskContext())
                    ->filterByTestSuite($context->getTestSuite())
                    ->filterByTaskNames($context->getTaskNames())
            )
        );
    }
}
