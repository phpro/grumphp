<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Runner\TaskResult;

class TaskResultCollection extends ArrayCollection
{
    const NO_TASKS = -100;

    /**
     * @return bool
     */
    public function isPassed()
    {
        return TaskResult::PASSED == $this->getResultCode();
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
}
