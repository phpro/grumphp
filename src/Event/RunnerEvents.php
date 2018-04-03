<?php

declare(strict_types=1);

namespace GrumPHP\Event;

final class RunnerEvents
{
    const RUNNER_RUN = 'grumphp.runner.run';
    const RUNNER_COMPLETE = 'grumphp.runner.complete';
    const RUNNER_FAILED = 'grumphp.runner.failed';
}
