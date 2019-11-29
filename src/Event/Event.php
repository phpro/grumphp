<?php

declare(strict_types=1);

namespace GrumPHP\Event;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEventContract;
use Symfony\Component\EventDispatcher\Event as SymfonyLegacyEvent;

// @codingStandardsIgnoreStart
if (class_exists(SymfonyEventContract::class)) {
    class Event extends SymfonyEventContract
    {
    }
} else {
    class Event extends SymfonyLegacyEvent
    {
    }
}
// @codingStandardsIgnoreEnd
