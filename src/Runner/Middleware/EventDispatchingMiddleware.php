<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Runner\RunnerInfo;

class EventDispatchingMiddleware implements MiddlewareInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(RunnerInfo $info, callable $next): TaskResultCollection
    {
        $this->eventDispatcher->dispatch(
            new RunnerEvent($info->getTasks(), $info->getContext(), new TaskResultCollection()),
            RunnerEvents::RUNNER_RUN
        );

        $results = $next($info);

        if ($results->isFailed()) {
            $this->eventDispatcher->dispatch(
                new RunnerFailedEvent($info->getTasks(), $info->getContext(), $results),
                RunnerEvents::RUNNER_FAILED
            );

            return $results;
        }

        $this->eventDispatcher->dispatch(
            new RunnerEvent($info->getTasks(), $info->getContext(), $results),
            RunnerEvents::RUNNER_COMPLETE
        );

        return $results;
    }
}
