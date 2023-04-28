<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\StopOnFailure;
use function Amp\async;
use Amp\Future;
use GrumPHP\Runner\MemoizedTaskResultMap;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class MemoizedResultsTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @var MemoizedTaskResultMap
     */
    private $resultMap;

    public function __construct(MemoizedTaskResultMap $resultMap)
    {
        $this->resultMap = $resultMap;
    }

    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        return async(
            function () use ($task, $runnerContext, $stopOnFailure, $next) : TaskResultInterface {
                try {
                    $result = $next($task, $runnerContext, $stopOnFailure)->await();
                } catch (\Throwable $error) {
                    $result = TaskResult::createFailed($task, $runnerContext->getTaskContext(), $error->getMessage());
                }

                $this->resultMap->onResult($result);

                return $result;
            }
        );
    }
}
