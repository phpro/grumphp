<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface TaskInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions();

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context);

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context);
}
