<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

/**
 * @psalm-readonly
 */
class TaskResult implements TaskResultInterface
{
    private $resultCode;
    private $task;
    private $context;
    private $message;

    private function __construct(
        int $resultCode,
        TaskInterface $task,
        ContextInterface $context,
        string $message = ''
    ) {
        $this->resultCode = $resultCode;
        $this->task = $task;
        $this->context = $context;
        $this->message = $message;
    }

    public static function createSkipped(TaskInterface $task, ContextInterface $context): self
    {
        return new self(self::SKIPPED, $task, $context);
    }

    public static function createPassed(TaskInterface $task, ContextInterface $context): self
    {
        return new self(self::PASSED, $task, $context);
    }

    public static function createFailed(
        TaskInterface $task,
        ContextInterface $context,
        string $message
    ): self {
        return new self(self::FAILED, $task, $context, $message);
    }

    public static function createNonBlockingFailed(
        TaskInterface $task,
        ContextInterface $context,
        string $message
    ): self {
        return new self(self::NONBLOCKING_FAILED, $task, $context, $message);
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    public function isPassed(): bool
    {
        return self::PASSED === $this->resultCode;
    }

    public function hasFailed(): bool
    {
        return self::FAILED === $this->resultCode || self::NONBLOCKING_FAILED === $this->resultCode;
    }

    public function isSkipped(): bool
    {
        return $this->resultCode === self::SKIPPED;
    }

    public function isBlocking(): bool
    {
        return $this->resultCode >= self::FAILED;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function withAppendedMessage(string $additionalMessage): TaskResultInterface
    {
        $new = clone $this;
        $new->message = $this->message . $additionalMessage;

        return $new;
    }
}
