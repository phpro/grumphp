<?php

namespace GrumPHP\TestSuite;

class TestSuite implements TestSuiteInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $taskNames = [];

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
