<?php

namespace GrumPHP\Configuration;

use GrumPHP\Locator\LocatorInterface;
use Symfony\Component\Process\ProcessBuilder;
use Zend\Stdlib\AbstractOptions;

abstract class AbstractConfiguration extends AbstractOptions implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $taskClass;

    /**
     * @return string
     */
    public function getTaskClass()
    {
        return $this->taskClass;
    }

    /**
     * @param string $taskClass
     */
    public function setTaskClass($taskClass)
    {
        $this->taskClass = $taskClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTaskInstance(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        return new $this->taskClass($grumPHP, $externalCommandLocator, $processBuilder);
    }
}
