<?php

namespace GrumPHP\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
final class EventDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return EventDispatcherInterface
     */
    public static function create(ContainerInterface $container)
    {
        if (class_exists(ContainerAwareEventDispatcher::class)) {
            return new ContainerAwareEventDispatcher($container);
        }

        return new EventDispatcher();
    }
}
