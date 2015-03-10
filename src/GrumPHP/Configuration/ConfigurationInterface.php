<?php

namespace GrumPHP\Configuration;

use GrumPHP\Locator\LocatorInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Process\ProcessBuilder;

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
     * @param LocatorInterface $externalCommandLocator
     * @param ProcessBuilder $processBuilder
     *
     * @return TaskInterface
     */
    public function buildTaskInstance(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder);
}
