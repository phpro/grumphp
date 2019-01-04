<?php

declare(strict_types=1);

namespace GrumPHP\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 *
 * This is a backward compatibility layer for Symfony < 3.3.
 *
 * @todo Drop me after bumping symfony/dependency-injection + symfony/event-dispatcher to ^3.3
 *       and directly use the `Symfony\Component\EventDispatcher\EventDispatcher` class
 *       for the `event_dispatcher` service definition in services.yml
 */
final class EventDispatcherFactory
{
    public static function create(ContainerInterface $container): EventDispatcherInterface
    {
        if (class_exists(ContainerAwareEventDispatcher::class)) {
            return new ContainerAwareEventDispatcher($container);
        }

        return new EventDispatcher();
    }
}
