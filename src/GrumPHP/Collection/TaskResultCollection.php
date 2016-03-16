<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class TaskResultCollection extends ArrayCollection
{
    /**
     * @return bool
     */
    public function isPassed()
    {
        return $this->forAll(function ($key, $taskResult) {
            return $taskResult->isPassed();
        });
    }
}
