<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Exception\ParallelException;
use GrumPHP\IO\IOInterface;
use function Amp\call;
use function Amp\ParallelFunctions\parallel;
use Amp\Promise;
use function Amp\Promise\wait;
use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;
use Opis\Closure\SerializableClosure;

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

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Promise
    {
        if (!$this->config->isEnabled()) {
            return $next($task, $runnerContext);
        }

        /**
         * This method creates a callable that can be used to enqueue to run the task in parallel.
         * The result is wrapped in a serializable closure
         * to make sure all information inside the task can be serialized.
         * This implies that the result of the parallel command is another callable that will return the task result.
         *
         * The factory is wrapped in another close to make sure the error handling picks up the factory exceptions.
         *
         * @var callable(): Promise<TaskResultInterface> $enqueueParallelTask
         */
        $enqueueParallelTask = function () use ($task, $runnerContext, $next): Promise {
            return parallel(
                static function () use ($task, $runnerContext, $next): SerializableClosure {
                    /** @var TaskResultInterface $result */
                    $result = wait($next($task, $runnerContext));

                    return new SerializableClosure(
                        /**
                         * @return TaskResultInterface
                         */
                        static function () use ($result) {
                            return $result;
                        }
                    );
                },
                $this->poolFactory->create()
            )();
        };

        return call(
            /**
             * @return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            function () use ($enqueueParallelTask, $task, $runnerContext): \Generator {
                try {
                    /** @var callable(): TaskResultInterface $resultProvider */
                    $resultProvider = yield $enqueueParallelTask();
                    $result = $resultProvider();
                } catch (\Throwable $error) {
                    return TaskResult::createFailed(
                        $task,
                        $runnerContext->getTaskContext(),
                        $this->wrapException($error)->getMessage()
                    );
                }

                return $result;
            }
        );
    }

    private function wrapException(\Throwable $error): ParallelException
    {
        return $this->IO->isVerbose()
            ? ParallelException::fromVerboseThrowable($error)
            : ParallelException::fromThrowable($error);
    }
}
