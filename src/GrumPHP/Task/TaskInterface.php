<?php

namespace GrumPHP\Task;

/**
 * Interface TaskInterface
 *
 * @package GrumPHP\Task
 */
interface TaskInterface
{
    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @param array $files
     *
     * @return void
     */
    public function run(array $files);
}
