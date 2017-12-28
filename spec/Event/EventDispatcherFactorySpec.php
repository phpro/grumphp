<?php

namespace spec\GrumPHP\Event;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Event\EventDispatcherFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EventDispatcherFactory::class);
    }

    function it_creates_an_event_dispatcher(
        ContainerInterface $container
    ) {
        $eventDispatcher = self::create($container);
        $eventDispatcher->shouldBeAnInstanceOf(EventDispatcher::class);
    }

    function it_creates_a_container_aware_event_dispatcher_instead_when_available(
        ContainerInterface $container
    ) {
        if (!class_exists(ContainerAwareEventDispatcher::class)) {
            throw new SkippingException('A container-aware implementation of the event dispatcher is no longer available.');
        }

        $eventDispatcher = self::create($container);
        $eventDispatcher->shouldBeAnInstanceOf(ContainerAwareEventDispatcher::class);
        $eventDispatcher->getContainer()->shouldReturn($container);
    }
}
