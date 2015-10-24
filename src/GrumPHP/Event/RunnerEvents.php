<?php

namespace GrumPHP\Event;

/**
 * Class RunnerEvents
 *
 * @package GrumPHP\Events
 */
final class RunnerEvents
{
    const RUNNER_RUN = 'grumphp.runner.run';
    const RUNNER_COMPLETE = 'grumphp.runner.complete';
    const RUNNER_FAILED = 'grumphp.runner.failed';
}
