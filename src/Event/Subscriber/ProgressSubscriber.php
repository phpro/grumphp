<?php

declare(strict_types=1);

namespace GrumPHP\Event\Subscriber;

use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;

class ProgressSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RunnerEvents::RUNNER_RUN => 'startProgress',
            TaskEvents::TASK_RUN => 'advanceProgress',
            TaskEvents::TASK_COMPLETE => 'onTaskProgress',
            TaskEvents::TASK_FAILED => 'onTaskProgress',
            TaskEvents::TASK_SKIPPED => 'onTaskProgress',
            RunnerEvents::RUNNER_COMPLETE => 'finishProgress',
            RunnerEvents::RUNNER_FAILED => 'finishProgress',
        ];
    }

    public function createProgressBar(int $totalTasks): ProgressBar
    {
        $minSecondsBetweenRedraws = 0.0; // New progressbar doesnt redraw frequently enough.
        $this->progressBar = new ProgressBar($this->output, $totalTasks, $minSecondsBetweenRedraws);
        $this->progressBar->setOverwrite(false);
        $this->progressBar->setFormat('<fg=yellow>Running task %current%/%max%:</fg=yellow> %message%... ');

        return $this->progressBar;
    }

    public function startProgress(RunnerEvent $event): void
    {
        $numberOftasks = $event->getTasks()->count();
        $this->createProgressBar($numberOftasks);
        $this->output->write('<fg=yellow>GrumPHP is sniffing your code!</fg=yellow>');
    }

    public function advanceProgress(TaskEvent $event): void
    {
        $taskReflection = new ReflectionClass($event->getTask());
        $taskName = $taskReflection->getShortName();

        $this->progressBar->setMessage($taskName);
        $this->progressBar->advance();
    }

    public function onTaskProgress(TaskEvent $task, string $event): void
    {
        switch ($event) {
            case TaskEvents::TASK_COMPLETE:
                $this->output->write('<fg=green>✔</fg=green>');
                break;

            case TaskEvents::TASK_FAILED:
                $this->output->write('<fg=red>✘</fg=red>');
                break;

            case TaskEvents::TASK_SKIPPED:
                $this->output->write('', true);
                $this->output->write('<fg=yellow>Oh no, we hit the windows cmd input limit!</fg=yellow>', true);
                $this->output->write('<fg=yellow>Skipping task...</fg=yellow>');
        }
    }

    public function finishProgress(RunnerEvent $runnerEvent): void
    {
        if ($this->progressBar->getProgress() !== $this->progressBar->getMaxSteps()) {
            $this->progressBar->setFormat('<fg=red>%message%</fg=red>');
            $this->progressBar->setMessage('Aborted ...');
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }
}
