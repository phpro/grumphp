<?php

declare(strict_types=1);

namespace GrumPHP\Event;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\EventDispatcher\Event;

class StageEvent extends RunnerEvent
{
    /**
     * @var int
     */
    private $stage;

    public function __construct(
        int $stage,
        TasksCollection $tasks,
        ContextInterface $context,
        TaskResultCollection $taskResults
    ) {
        $this->stage = $stage;
        parent::__construct($tasks, $context, $taskResults);
    }

    public function getStage(): int
    {
        return $this->stage;
    }
}
