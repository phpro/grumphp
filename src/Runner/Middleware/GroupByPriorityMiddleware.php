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

    /**
     * @var bool
     */
    private $stopOnFailure;

    public function __construct(IOInterface $IO, bool $stopOnFailure)//, GrumPHP $config)
    {
        $this->IO = $IO;
        $this->stopOnFailure = $stopOnFailure;
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

            // Stop on failure:
            if ($this->stopOnFailure && $results->isFailed()) {
                return $results;
            }
        }

        return $results;
    }
}
