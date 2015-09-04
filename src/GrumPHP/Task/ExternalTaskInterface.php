<?php

namespace GrumPHP\Task;

/**
 * Interface TaskInterface
 *
 * @package GrumPHP\Task
 */
interface ExternalTaskInterface extends TaskInterface
{
    /**
     * @return string
     */
    public function getCommandLocation();
}
