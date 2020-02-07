<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Stack;

use GrumPHP\Runner\Middleware\MiddlewareInterface;

interface StackInterface
{
    public function next(): MiddlewareInterface;
}
