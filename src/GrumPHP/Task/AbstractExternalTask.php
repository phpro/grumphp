<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\ProcessBuilder;

/**
 * Class AbstractExternalTask
 *
 * @package GrumPHP\Task
 */
abstract class AbstractExternalTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @param GrumPHP $grumPHP
     * @param ProcessBuilder $processBuilder
     */
    public function __construct(GrumPHP $grumPHP, ProcessBuilder $processBuilder)
    {
        $this->grumPHP = $grumPHP;
        $this->processBuilder = $processBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }
}
