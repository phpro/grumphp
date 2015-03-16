<?php

namespace GrumPHP\Task;
use GrumPHP\Exception\RuntimeException;

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
     * @return array
     */
    public function getDefaultConfiguration();

    /**
     * @param array $files
     *
     * @return void
     * @throws RuntimeException
     */
    public function run(array $files);
}
