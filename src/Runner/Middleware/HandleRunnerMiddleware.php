<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use Amp\Future;
use GrumPHP\Configuration\Model\RunnerConfig;
use function Amp\async;
use function Amp\Future\await;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Promise\MultiPromise;
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
        // TODO : CANCELLATION based on return $this->config->stopOnFailure() && $result->isBlocking();

        return new TaskResultCollection(
            await(
                array_map(
                    /** @return Future<TaskResultInterface> */
                    fn (TaskInterface $task): Future => $this->taskHandler->handle($task, $context),
                    $context->getTasks()->toArray()
                )
            )
        );
    }
}
