<?php declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TaskInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver;

    /**
     * This methods specifies if a task can run in a specific context.
     */
    public function canRunInContext(ContextInterface $context): bool;

    /**
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context): TaskResultInterface;
}
