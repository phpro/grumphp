<?php

declare(strict_types=1);

namespace GrumPHP\Event\Dispatcher;

use GrumPHP\Event\Event;

interface EventDispatcherInterface
{
    public function dispatch(Event $event, string $name = null): void;
}
