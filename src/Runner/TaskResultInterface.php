<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

/**
 * @psalm-immutable
 */
interface TaskResultInterface
{
    public function getTask(): TaskInterface;

    public function getResultCode(): int;

    public function isPassed(): bool;

    public function hasFailed(): bool;

    public function isSkipped(): bool;

    public function isBlocking(): bool;

    public function getMessage(): string;

    public function getContext(): ContextInterface;
}
