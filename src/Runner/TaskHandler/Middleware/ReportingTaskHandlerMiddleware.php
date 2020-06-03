<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use Amp\Promise;
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

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Promise
    {
        return call(
            /**
             * @return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            function () use ($task, $runnerContext, $next) {
                /** @var TaskResultInterface $result */
                $result = yield $next($task, $runnerContext);

                $this->reporter->report($runnerContext);

                return $result;
            }
        );
    }
}
