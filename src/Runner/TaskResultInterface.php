<?php declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

interface TaskResultInterface
{
    /**
     * @return TaskInterface
     */
    public function getTask(): TaskInterface;

    /**
     * @return int
     */
    public function getResultCode(): int;

    /**
     * @return bool
     */
    public function isPassed(): bool;

    /**
     * @return bool
     */
    public function isBlocking(): bool;

    /**
     * @return null|string
     */
    public function getMessage();

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;
}
