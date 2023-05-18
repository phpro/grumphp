<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;

interface TaskInterface
{
    public static function getConfigurableOptions(): ConfigOptionsResolver;

    public function canRunInContext(ContextInterface $context): bool;

    public function run(ContextInterface $context): TaskResultInterface;

    public function getConfig(): TaskConfigInterface;

    public function withConfig(TaskConfigInterface $config): TaskInterface;
}
