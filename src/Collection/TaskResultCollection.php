<?php declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;

class TaskResultCollection extends ArrayCollection
{
    const NO_TASKS = -100;

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return TaskResult::PASSED == $this->getResultCode();
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        foreach ($this as $taskResult) {
            if (TaskResult::FAILED === $taskResult->getResultCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int|mixed
     */
    public function getResultCode()
    {
        $resultCode = static::NO_TASKS;
        foreach ($this as $taskResult) {
            $resultCode = max($resultCode, $taskResult->getResultCode());
        }

        return $resultCode;
    }

    /**
     * @param int $resultCode
     * @return static
     */
    public function filterByResultCode(int $resultCode)
    {
        return $this->filter(function (TaskResultInterface $taskResult) use ($resultCode) {
            return $resultCode === $taskResult->getResultCode();
        });
    }

    /**
     * @return array
     */
    public function getAllMessages(): array
    {
        $messages = [];

        foreach ($this as $taskResult) {
            $messages[] = $taskResult->getMessage();
        }

        return $messages;
    }
}
