<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Runner\TaskRunnerContext;

class EventDispatchingRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        $this->eventDispatcher->dispatch(
            new RunnerEvent($context->getTasks(), $context->getTaskContext(), new TaskResultCollection()),
            RunnerEvents::RUNNER_RUN
        );

        /** @var TaskResultCollection $results */
        $results = $next($context);

        if ($results->isFailed()) {
            $this->eventDispatcher->dispatch(
                new RunnerFailedEvent($context->getTasks(), $context->getTaskContext(), $results),
                RunnerEvents::RUNNER_FAILED
            );

            return $results;
        }

        $this->eventDispatcher->dispatch(
            new RunnerEvent($context->getTasks(), $context->getTaskContext(), $results),
            RunnerEvents::RUNNER_COMPLETE
        );

        return $results;
    }
}
