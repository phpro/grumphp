<?php

namespace GrumPHP\TestSuite;

/**
 * Class TestSuite
 *
 * @package GrumPHP\TestSuite
 */
class TestSuite implements TestSuiteInterface
{
    /**
     * @var array
     */
    private $taskNames = [];
    /**
     * @var string
     */
    private $name;

    /**
     * TestSuite constructor.
     *
     * @param string $name
     * @param array  $taskNames
     */
    public function __construct($name, array $taskNames)
    {
        $this->taskNames = $taskNames;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTaskNames()
    {
        return $this->taskNames;
    }
}
