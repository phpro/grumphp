<?php

declare(strict_types=1);

namespace GrumPHP\Event;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEventContract;
use Symfony\Component\EventDispatcher\Event as SymfonyLegacyEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
// @codingStandardsIgnoreStart
if (class_exists(SymfonyEventContract::class) &&
    in_array('EventDispatcherInterface', class_implements(EventDispatcher::class))) {
    class Event extends SymfonyEventContract
    {
    }
} else {
    class Event extends SymfonyLegacyEvent
    {
    }
}
// @codingStandardsIgnoreEnd
