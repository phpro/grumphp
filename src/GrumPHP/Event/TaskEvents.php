<?php

namespace GrumPHP\Event;

/**
 * Class TaskEvents
 *
 * @package GrumPHP\Event
 */
final class TaskEvents
{
    const TASK_RUN = 'grumphp.task.run';
    const TASK_COMPLETE = 'grumphp.task.complete';
    const TASK_FAILED = 'grumphp.task.failed';
}
