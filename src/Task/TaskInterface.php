<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TaskInterface
{
    public function getName(): string;

    public function getConfiguration(): array;

    public function getConfigurableOptions(): OptionsResolver;

    public function canRunInContext(ContextInterface $context): bool;

    public function run(ContextInterface $context): TaskResultInterface;
}
