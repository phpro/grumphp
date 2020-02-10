<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;

abstract class AbstractExternalTask implements TaskInterface
{
    /**
     * @var TaskConfigInterface
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
        $this->config = new EmptyTaskConfig();
        $this->processBuilder = $processBuilder;
        $this->formatter = $formatter;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }
}
