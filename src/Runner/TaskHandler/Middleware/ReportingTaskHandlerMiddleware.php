<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\async;
use Amp\Future;
use GrumPHP\Runner\Reporting\TaskResultsReporter;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class ReportingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @var TaskResultsReporter
     */
    private $reporter;

    public function __construct(TaskResultsReporter $reporter)
    {
        $this->reporter = $reporter;
    }

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Future
    {
        return async(
            function () use ($task, $runnerContext, $next): TaskResultInterface {
                $result = $next($task, $runnerContext)->await();

                $this->reporter->report($runnerContext);

                return $result;
            }
        );
    }
}
