<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use function Amp\ParallelFunctions\parallel;
use Amp\Promise;
use function Amp\Promise\wait;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Opis\Closure\SerializableClosure;

class ParallelProcessingMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(TaskInterface $task, ContextInterface $context, callable $next): Promise
    {
        /**
         * @psalm-var callable(): Promise<TaskResultInterface> $enqueueParallelTask
         */
        $enqueueParallelTask = parallel(
            static function () use ($task, $context, $next): SerializableClosure {
                $result = wait($next($task, $context));

                return new SerializableClosure(static function () use ($result) {
                    return $result;
                });
            }
        );

        return call(
            /**
             * @psalm-return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            static function () use ($enqueueParallelTask): \Generator {
                /** @var callable(): TaskResultInterface $resultProvider */
                $resultProvider = yield $enqueueParallelTask();

                return $resultProvider();
            }
        );
    }
}
