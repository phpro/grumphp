<?php declare(strict_types=1);

namespace GrumPHP\Event;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Throwable;

class TaskFailedEvent extends TaskEvent
{
    /**
     * @var Throwable
     */
    private $exception;

    public function __construct(TaskInterface $task, ContextInterface $context, Throwable $exception)
    {
        parent::__construct($task, $context);

        $this->exception = $exception;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }
}
