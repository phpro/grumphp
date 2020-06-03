<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Reporting\TaskResultsReporter;
use GrumPHP\Runner\TaskRunnerContext;

class ReportingTasksSectionRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var TaskResultsReporter
     */
    private $reporter;

    public function __construct(TaskResultsReporter $reporter)
    {
        $this->reporter = $reporter;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        return $this->reporter->runInSection(
            /**
             * @return TaskResultCollection
             */
            function () use ($context, $next): TaskResultCollection {
                $this->reporter->report($context);

                return $next($context);
            }
        );
    }
}
