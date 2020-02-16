<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class HandleRunnerMiddleware implements RunnerMiddlewareInterface
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
            array_map(
                function (TaskInterface $task) use ($context): TaskResultInterface {
                    return $this->taskHandler->handle($task, $context->getTaskContext());
                },
                $context->getTasks()->toArray()
            )
        );
    }
}
