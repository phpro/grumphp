<?php

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;

class ParallelOptions
{
    /**
     * @var int
     */
    private $sleep;
    /**
     * @var int
     */
    private $maxProcesses;

    /**
     * ParallelOptions constructor.
     *
     * @param int $sleep
     * @param int $maxProcesses
     */
    public function __construct(int $sleep = 1, int $maxProcesses = 2)
    {
        $this->sleep        = $sleep;
        $this->maxProcesses = $maxProcesses;
    }

    /**
     * @return int
     */
    public function getSleep(): int
    {
        return $this->sleep;
    }

    /**
     * @return int
     */
    public function getMaxProcesses(): int
    {
        return $this->maxProcesses;
    }
}
