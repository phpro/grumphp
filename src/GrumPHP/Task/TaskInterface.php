<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

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
     * @param Finder $files
     *
     * @return void
     * @throws RuntimeException
     */
    public function run(Finder $files);
}
