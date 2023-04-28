<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\Parallel\SerializedClosureTask;
use function Amp\async;
use Amp\Future;
use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class ParallelProcessingMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @var ParallelConfig
     */
    private $config;

    /**
     * @var PoolFactory
     */
    private $poolFactory;

    /**
     * @var IOInterface
     */
    private $IO;

    public function __construct(ParallelConfig $config, PoolFactory $poolFactory, IOInterface $IO)
    {
        $this->poolFactory = $poolFactory;
        $this->config = $config;
        $this->IO = $IO;
    }

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Future
    {
        if (!$this->config->isEnabled()) {
            return async(static fn () => $next($task, $runnerContext)->await());
        }

        $currentEnv = $_ENV;

        $worker = $this->poolFactory->createShared();
        $execution = $worker->submit(
            SerializedClosureTask::fromClosure(
                static function () use ($task, $runnerContext, $next) {
                    // TODO : pass down $_ENV = array_merge($parentEnv, $_ENV); ?
                    $result = $next($task, $runnerContext)->await();

                    return $result;
                }
            )
        );

        // TODO : pass down cancellation?
        // TODO : wrap error handling inside closure?
        // TODO : wrap error handling outside closure?

        return $execution->getFuture();
    }
}
