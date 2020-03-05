<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use function Amp\ParallelFunctions\parallel;
use Amp\Promise;
use function Amp\Promise\wait;
use GrumPHP\Runner\Parallel\PoolFactory;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;
use Opis\Closure\SerializableClosure;

class ParallelProcessingMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @var PoolFactory
     */
    private $poolFactory;

    public function __construct(PoolFactory $poolFactory)
    {
        $this->poolFactory = $poolFactory;
    }

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Promise
    {
        /**
         * This method creates a callable that can be used to enqueue to run the task in parallel.
         * The result is wrapped in a serializable closure
         * to make sure all information inside the task can be serialized.
         * This implies that the result of the parallel command is another callable that will return the task result.
         *
         * @var callable(): Promise<TaskResultInterface> $enqueueParallelTask
         */
        $enqueueParallelTask = parallel(
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
        );

        return call(
            /**
             * @return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            static function () use ($enqueueParallelTask, $task, $runnerContext): \Generator {
                try {
                    /** @var callable(): TaskResultInterface $resultProvider */
                    $resultProvider = yield $enqueueParallelTask();
                    $result = $resultProvider();
                } catch (\Throwable $error) {
                    // TODO : only log more in verbose mode ...
                    $message = $error->getMessage() . PHP_EOL . $error->getTraceAsString() . PHP_EOL . $error->getPrevious();

                    return TaskResult::createFailed($task, $runnerContext->getTaskContext(), $message);
                }


                return $result;
            }
        );
    }
}
