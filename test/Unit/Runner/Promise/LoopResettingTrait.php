<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Promise;

use Amp\Loop;
use Amp\Loop\DriverFactory;

trait LoopResettingTrait
{
    protected function safelyRunAsync(callable $test): void
    {
        $resetLoop = function () {
            Loop::set((new DriverFactory)->create());
        };

        $resetLoop();
        try {
            $test();
        } finally {
            $resetLoop();
        }
    }
}
