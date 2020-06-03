<?php

declare(strict_types=1);

namespace GrumPHP\Test\Runner;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

abstract class AbstractRunnerMiddlewareTestCase extends AbstractMiddlewareTestCase
{
}
