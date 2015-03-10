<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\ConfigurationInterface;

/**
 * Interface TaskInterface
 *
 * @package GrumPHP\Task
 */
interface TaskInterface
{
    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * @param array $files
     *
     * @return void
     */
    public function run(array $files);
}
