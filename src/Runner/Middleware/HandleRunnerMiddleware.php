<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use Amp\CancelledException;
use Amp\Future;
use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\StopOnFailure;
use function Amp\Future\await;
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

    /**
     * @var RunnerConfig
     */
    private $config;

    public function __construct(TaskHandler $taskHandler, RunnerConfig $config)
    {
        $this->taskHandler = $taskHandler;
        $this->config = $config;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        $stopOnFailure = StopOnFailure::createFromConfig($this->config);

        $futures = array_map(
            /** @return Future<TaskResultInterface> */
            fn (TaskInterface $task): Future => $this->taskHandler->handle($task, $context, $stopOnFailure),
            $context->getTasks()->toArray()
        );

        try {
            return new TaskResultCollection(
                await($futures, $stopOnFailure->cancellation())
            );
        } catch (CancelledException $e) {
            return array_reduce(
                $futures,
                static function (TaskResultCollection $result, Future $future): TaskResultCollection {
                    if ($future->isComplete()) {
                        $result->add($future->await());
                    }

                    return $result;
                },
                new TaskResultCollection()
            );
        }
    }
}
