<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

/**
 * Class TasksCollection
 *
 * @package GrumPHP\Collection
 */
class TasksCollection extends ArrayCollection
{

    /**
     * @param ContextInterface $context
     *
     * @return TasksCollection
     */
    public function filterByContext(ContextInterface $context)
    {
        return $this->filter(function (TaskInterface $task) use ($context) {
            return $task->canRunInContext($context);
        });
    }
}
