<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;

/**
 * @psalm-immutable
 */
class RunnerInfo
{
    /**
     * @var TasksCollection
     */
    private $tasks;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var TaskRunnerContext
     */
    private $runnerContext;

    public function __construct(TasksCollection $tasks, ContextInterface $context, TaskRunnerContext $runnerContext)
    {
        $this->tasks = $tasks;
        $this->context = $context;
        $this->runnerContext = $runnerContext;
    }

    public function getTasks(): TasksCollection
    {
        return $this->tasks;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getRunnerContext(): TaskRunnerContext
    {
        return $this->runnerContext;
    }

    public function withTasks(TasksCollection $tasks): self
    {
        $new = clone $this;
        $new->tasks = $tasks;

        return $new;
    }
}
