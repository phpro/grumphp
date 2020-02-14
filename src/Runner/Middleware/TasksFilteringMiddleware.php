<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Runner\RunnerInfo;

class TasksFilteringMiddleware implements MiddlewareInterface
{
    public function handle(RunnerInfo $info, callable $next): TaskResultCollection
    {
        $runnerContext = $info->getRunnerContext();

        return $next(
            $info->withTasks(
                (new TasksCollection($info->getTasks()->toArray()))
                    ->filterByContext($runnerContext->getTaskContext())
                    ->filterByTestSuite($runnerContext->getTestSuite())
                    ->filterByTaskNames($runnerContext->getTasks())
                    ->sortByPriority()
            )
        );
    }
}
