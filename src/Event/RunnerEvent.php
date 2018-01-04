<?php declare(strict_types=1);

namespace GrumPHP\Event;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\EventDispatcher\Event;

class RunnerEvent extends Event
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
     * @var TaskResultCollection
     */
    private $taskResults;

    public function __construct(TasksCollection $tasks, ContextInterface $context, TaskResultCollection $taskResults)
    {
        $this->tasks = $tasks;
        $this->context = $context;
        $this->taskResults = $taskResults;
    }

    /**
     * @return TasksCollection
     */
    public function getTasks(): TasksCollection
    {
        return $this->tasks;
    }

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @return TaskResultCollection
     */
    public function getTaskResults(): TaskResultCollection
    {
        return $this->taskResults;
    }
}
