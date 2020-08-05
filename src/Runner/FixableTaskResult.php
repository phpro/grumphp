<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Fixer\FixResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

/**
 * @psalm-readonly
 */
class FixableTaskResult implements TaskResultInterface
{
    /**
     * @var TaskResultInterface
     */
    private $result;

    /**
     * @var callable(): FixResult
     */
    private $fixer;

    public function __construct(TaskResultInterface $result, callable $fixer)
    {
        $this->result = $result;
        $this->fixer = $fixer;
    }

    public function getTask(): TaskInterface
    {
        return $this->result->getTask();
    }

    public function getResultCode(): int
    {
        return $this->result->getResultCode();
    }

    public function isPassed(): bool
    {
        return $this->result->isPassed();
    }

    public function hasFailed(): bool
    {
        return $this->result->hasFailed();
    }

    public function isSkipped(): bool
    {
        return $this->result->isSkipped();
    }

    public function isBlocking(): bool
    {
        return $this->result->isBlocking();
    }

    public function getMessage(): string
    {
        return $this->result->getMessage();
    }

    public function getContext(): ContextInterface
    {
        return $this->result->getContext();
    }

    public function withAppendedMessage(string $additionalMessage): TaskResultInterface
    {
        $new = clone $this;
        $new->result = $this->result->withAppendedMessage($additionalMessage);

        return $new;
    }

    public function fix(): FixResult
    {
        return ($this->fixer)();
    }
}
