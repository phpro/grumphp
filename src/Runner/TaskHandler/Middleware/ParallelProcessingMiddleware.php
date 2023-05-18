<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Exception\ParallelException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\Parallel\SerializedClosureTask;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use function Amp\async;
use Amp\Future;
use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class ParallelProcessingMiddleware implements TaskHandlerMiddlewareInterface
{
    private ParallelConfig $config;
    private PoolFactory $poolFactory;
    private IOInterface $IO;

    public function __construct(
        ParallelConfig $config,
        PoolFactory $poolFactory,
        IOInterface $IO
    ) {
        $this->poolFactory = $poolFactory;
        $this->config = $config;
        $this->IO = $IO;
    }

    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        if (!$this->config->isEnabled()) {
            return async(static fn () => $next($task, $runnerContext, $stopOnFailure)->await());
        }

        $currentEnv = $_ENV;
        $worker = $this->poolFactory->createShared();
        $execution = $worker->submit(
            SerializedClosureTask::fromClosure(
                static function () use ($task, $runnerContext, $next, $currentEnv): TaskResultInterface {
                    $_ENV = array_merge($currentEnv, $_ENV);

                    return $next($task, $runnerContext, StopOnFailure::dummy())->await();
                }
            ),
            $stopOnFailure->cancellation()
        );

        return async(function () use ($task, $runnerContext, $execution): TaskResultInterface {
            try {
                return $execution->getFuture()->await();
            } catch (\Throwable $exception) {
                return TaskResult::createFailed(
                    $task,
                    $runnerContext->getTaskContext(),
                    $this->wrapException($exception)->getMessage()
                );
            }
        });
    }

    private function wrapException(\Throwable $error): ParallelException
    {
        return $this->IO->isVerbose()
            ? ParallelException::fromVerboseThrowable($error)
            : ParallelException::fromThrowable($error);
    }
}
