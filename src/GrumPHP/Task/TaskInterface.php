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
     * @param array $files
     *
     * @return void
     */
    public function run(array $files);

}
