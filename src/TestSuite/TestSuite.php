<?php declare(strict_types=1);

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
    public function __construct(string $name, array $taskNames)
    {
        $this->taskNames = $taskNames;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTaskNames(): array
    {
        return $this->taskNames;
    }
}
