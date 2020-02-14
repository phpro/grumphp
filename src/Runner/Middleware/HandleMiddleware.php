<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskHandler\TaskHandlerInterface;
use GrumPHP\Runner\RunnerInfo;
use GrumPHP\Runner\Stack\StackInterface;
use GrumPHP\Runner\TaskHandler\TaskHandlerStack;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\TaskInterface;

class HandleMiddleware implements MiddlewareInterface
{
    /**
     * @var TaskHandlerStack
     */
    private $taskHandler;

    public function __construct(TaskHandlerStack $taskHandler)
    {
        $this->taskHandler = $taskHandler;
    }

    public function handle(RunnerInfo $info, callable $next): TaskResultCollection
    {
        return new TaskResultCollection(
            array_map(
                function (TaskInterface $task) use ($info): TaskResultInterface {
                    return $this->taskHandler->handle($task, $info->getContext());
                },
                $info->getTasks()->toArray()
            )
        );
    }
}
