<?php

declare(strict_types=1);

namespace GrumPHP\Event\Subscriber;

use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Task\ParallelTaskInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ReflectionClass;

class ParallelProgressSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProgressBar[]
     */
    private $progressBars;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output       = $output;
        $this->progressBars = [];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RunnerEvents::RUNNER_RUN      => 'startProgress',
            TaskEvents::TASK_RUN          => 'advanceProgress',
            TaskEvents::TASK_COMPLETE     => 'onTaskProgress',
            TaskEvents::TASK_FAILED       => 'onTaskProgress',
            TaskEvents::TASK_SKIPPED      => 'onTaskProgress',
            RunnerEvents::RUNNER_COMPLETE => 'finishProgress',
            RunnerEvents::RUNNER_FAILED   => 'finishProgress',
        ];
    }

    public function startProgress(RunnerEvent $event)
    {
        $this->output->write('<fg=yellow>GrumPHP is sniffing your code!</fg=yellow>', true);
        /**
         * @var TaskInterface $task
         */
        $current = 1;
        $max     = $event->getTasks()->count();
        foreach ($event->getTasks() as $task) {
            // Note:
            // $maxValue = 2 because
            // first event is start of the task
            // second event is finish of the task
            //
            // This might become much better with symfony 4 and console output sections
            // @see https://symfony.com/doc/current/console.html#console-output-sections
            $bar = new ProgressBar($this->output, 2);
            $bar->setOverwrite(false);
            $this->progressBars[$task->getName()] = $bar;

            $taskInfo = $this->getTaskInfo($task);

            // TODO: Remove instanceof check
            if ($this->output->getVerbosity() >= $this->output::VERBOSITY_VERY_VERBOSE
                && $task instanceof ParallelTaskInterface
            ) {
                $taskInfo .= " via\n".$task->resolveProcess($event->getContext())->getCommandLine();
            }

            $bar = $this->progressBars[$task->getName()];
            $bar->setFormat("<fg=yellow>Task $current/$max:</fg=yellow> <fg=%color%>[%status%]</fg=%color%> %message%");
            $this->setMessageForTask($task, $taskInfo, "Scheduling", "yellow");
            $bar->start();
            // TODO
            // Hack to avoid an unecessary new line
            if ($current < $max) {
                $this->output->write('', true);
            }
            $current++;
        }
        echo "";
    }

    public function advanceProgress(TaskEvent $event)
    {
        $task     = $event->getTask();
        $taskInfo = $this->getTaskInfo($task);
        $bar      = $this->setMessageForTask($task, $taskInfo, "Running", "cyan");
        $bar->advance();
    }

    public function onTaskProgress(TaskEvent $event, string $eventName)
    {
        $task     = $event->getTask();
        $taskInfo = $this->getTaskInfo($task);

        $message = $taskInfo." ";
        $status  = "unknown";
        $color   = "red";
        switch ($eventName) {
            case TaskEvents::TASK_COMPLETE:
                $message .= '<fg=green>✔</fg=green>';
                $status  = 'Success';
                $color   = 'green';
                break;

            case TaskEvents::TASK_FAILED:
                $message .= ('<fg=red>✘</fg=red>');
                $status  = 'Failed';
                $color   = 'red';
                break;

            case TaskEvents::TASK_SKIPPED:
                // TODO: Not sure what that is for...
//                $message .= ('', true);
//                $message .= ('<fg=yellow>Oh no, we hit the windows cmd input limit!</fg=yellow>', true);
                $message .= ('<fg=yellow>-</fg=yellow>');
                $status  = 'Skipped';
                $color   = 'yellow';
                break;
        }

        $bar = $this->setMessageForTask($task, $message, $status, $color);
        $bar->advance();
    }

    public function finishProgress(RunnerEvent $runnerEvent)
    {
        foreach ($runnerEvent->getTasks() as $task) {
            $taskInfo = $this->getTaskInfo($task);
            $bar      = $this->progressBars[$task->getName()];
            if ($bar->getProgress() !== $bar->getMaxSteps()) {
                $this->setMessageForTask($task, $taskInfo, "Aborted", "red");
            }

            $bar->finish();
        }
        $this->output->writeln('');
    }

    protected function getTaskInfo(TaskInterface $task): string
    {
        $taskReflection = new ReflectionClass($task);
        $taskInfo       = $taskReflection->getShortName();
        if ($this->output->getVerbosity() >= $this->output::VERBOSITY_VERBOSE) {
            $taskInfo .= " (".$task->getName().")";
        }
        return $taskInfo;
    }

    protected function setMessageForTask(
        TaskInterface $task,
        string $message,
        string $status,
        string $color
    ): ProgressBar {
        $bar = $this->progressBars[$task->getName()];
        $bar->setMessage($message);
        $bar->setMessage($status, "status");
        $bar->setMessage($color, "color");
        return $bar;
    }
}
