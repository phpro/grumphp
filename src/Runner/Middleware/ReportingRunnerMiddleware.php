<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Reporting\RunnerReporter;
use GrumPHP\Runner\TaskRunnerContext;

class ReportingRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var RunnerReporter
     */
    private $reporter;

    public function __construct(RunnerReporter $reporter)
    {
        $this->reporter = $reporter;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        $this->reporter->start($context);
        $results = $next($context);
        $this->reporter->finish($context, $results);

        return $results;
    }
}
