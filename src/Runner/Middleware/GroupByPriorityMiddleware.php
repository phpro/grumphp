<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\TaskRunnerContext;

class GroupByPriorityMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var IOInterface
     */
    private $IO;

    public function __construct(IOInterface $IO)
    {
        $this->IO = $IO;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        $results = new TaskResultCollection();

        foreach ($context->getTasks()->groupByPriority() as $priority => $tasks) {
            $this->IO->style()->title('Running tasks with priority '.$priority.'!');
            $results = new TaskResultCollection(array_merge(
                $results->toArray(),
                $next($context->withTasks($tasks))->toArray()
            ));
        }

        return $results;
    }
}
