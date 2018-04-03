<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;

abstract class AbstractExternalTask implements TaskInterface
{
    protected $grumPHP;
    protected $processBuilder;
    protected $formatter;

    public function __construct(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $this->grumPHP = $grumPHP;
        $this->processBuilder = $processBuilder;
        $this->formatter = $formatter;
    }

    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }
}
