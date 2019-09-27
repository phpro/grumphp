<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Factory;

use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\TaskInterface;

class ConfiguredTaskFactory
{
    /**
     * @var TaskConfig
     */
    private $config;

    public function __construct(TaskConfig $config)
    {
        $this->config = $config;
    }

    public function __invoke(TaskInterface $task): TaskInterface
    {
        return $task->withConfig($this->config);
    }
}
