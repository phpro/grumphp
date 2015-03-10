<?php

namespace GrumPHP\Configuration;

use Zend\Stdlib\AbstractOptions;

/**
 * Phpspec configuration
 */
class Phpspec extends AbstractOptions implements ConfigurationInterface
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
    public function buildTaskInstance(GrumPHP $grumPHP)
    {
        return new $this->taskClass($grumPHP);
    }
}
