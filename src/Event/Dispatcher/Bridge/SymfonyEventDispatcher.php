<?php

declare(strict_types=1);

namespace GrumPHP\Event\Dispatcher\Bridge;

use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyLegacyEventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherContract;

class SymfonyEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var SymfonyLegacyEventDispatcher|SymfonyEventDispatcherContract
     */
    private $dispatcher;

    /**
     * @param SymfonyLegacyEventDispatcher|SymfonyEventDispatcherContract $eventDispatcher
     */
    public function __construct($eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
    }

    public function dispatch(Event $event, string $eventName = null): void
    {
        $interfacesImplemented = class_implements($this->dispatcher);
        if (in_array(SymfonyEventDispatcherContract::class, $interfacesImplemented, true)) {
            /**
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $this->dispatcher->dispatch($event, $eventName);
            return;
        }

        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress TooManyArguments
         */
        $this->dispatcher->dispatch($eventName, $event);
    }
}
