<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Runner\Stack\StackInterface;
use PHP_CodeSniffer\Reports\Info;

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

    public function handle(Info $info, StackInterface $stack): TaskResultCollection
    {
        $next = $stack->next();
    }
}
