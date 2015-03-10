<?php

namespace GrumPHP\Configuration;

use GrumPHP\Task\TaskInterface;

interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getTaskClass();

    /**
     * @param string $taskClass
     */
    public function setTaskClass($taskClass);

    /**
     * Build the associated Task.
     *
     * @param GrumPHP $grumPHP
     *
     * @return TaskInterface
     */
    public function buildTaskInstance(GrumPHP $grumPHP);
}
