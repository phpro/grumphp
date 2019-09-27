<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\TaskConfig;

abstract class AbstractExternalTask implements TaskInterface
{
    /**
     * @var TaskConfig
     */
    protected $config;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var ProcessFormatterInterface
     */
    protected $formatter;

    public function __construct(ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $this->processBuilder = $processBuilder;
        $this->formatter = $formatter;
    }

    public function getConfig(): TaskConfig
    {
        return $this->config;
    }

    public function withConfig(TaskConfig $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }
}
