<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class ParallelHandleRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var TaskHandler
     */
    private $taskHandler;

    public function __construct(TaskHandler $taskHandler)
    {
        $this->taskHandler = $taskHandler;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        return new TaskResultCollection(
            (array) wait(
                parallelMap(
                    $context->getTasks()->toArray(),
                    function (TaskInterface $task) use ($context) : TaskResultInterface {
                        return $this->taskHandler->handle($task, $context->getTaskContext());
                    }
                )
            )
        );
    }
}
