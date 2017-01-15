<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;

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
     * @var ProcessFormatterInterface
     */
    protected $formatter;

    /**
     * @param GrumPHP $grumPHP
     * @param ProcessBuilder $processBuilder
     * @param ProcessFormatterInterface $formatter
     */
    public function __construct(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $this->grumPHP = $grumPHP;
        $this->processBuilder = $processBuilder;
        $this->formatter = $formatter;
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
