<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;

/**
 * @extends ArrayCollection<int, TaskResultInterface>
 */
class TaskResultCollection extends ArrayCollection
{
    const NO_TASKS = -100;

    public function isPassed(): bool
    {
        return TaskResult::PASSED === $this->getResultCode();
    }

    public function isFailed(): bool
    {
        foreach ($this as $taskResult) {
            if (TaskResult::FAILED === $taskResult->getResultCode()) {
                return true;
            }
        }

        return false;
    }

    public function getResultCode(): int
    {
        $resultCode = static::NO_TASKS;
        foreach ($this as $taskResult) {
            $resultCode = (int) max($resultCode, $taskResult->getResultCode());
        }

        return $resultCode;
    }

    public function filterByResultCode(int $resultCode): self
    {
        return $this->filter(function (TaskResultInterface $taskResult) use ($resultCode): bool {
            return $resultCode === $taskResult->getResultCode();
        });
    }

    /**
     * @return array<string, string>
     */
    public function getAllMessages(): array
    {
        $messages = [];

        /** @var TaskResultInterface $taskResult */
        foreach ($this as $taskResult) {
            $config = $taskResult->getTask()->getConfig();
            $label = $config->getMetadata()->label() ?: $config->getName();
            $messages[$label] = $taskResult->getMessage();
        }

        return $messages;
    }
}
