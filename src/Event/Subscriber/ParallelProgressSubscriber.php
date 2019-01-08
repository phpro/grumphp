<?php

declare(strict_types=1);

namespace GrumPHP\Event\Subscriber;

use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\StageEvent;
use GrumPHP\Event\StageEvents;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
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
     * @var float[]
     */
    private $runtimes;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output       = $output;
        $this->progressBars = [];
        $this->runtimes     = [];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RunnerEvents::RUNNER_RUN      => 'startProgress',
            StageEvents::STAGE_RUN        => 'startStage',
            TaskEvents::TASK_RUN          => 'advanceProgress',
            TaskEvents::TASK_COMPLETE     => 'onTaskProgress',
            TaskEvents::TASK_FAILED       => 'onTaskProgress',
            TaskEvents::TASK_SKIPPED      => 'onTaskProgress',
            StageEvents::STAGE_COMPLETE   => 'finishStage',
            RunnerEvents::RUNNER_COMPLETE => 'finishProgress',
            RunnerEvents::RUNNER_FAILED   => 'finishProgress',
        ];
    }

    public function startStage(StageEvent $event)
    {
        if ($this->output->isVerbose()) {
            $this->output->write($this->getMessageForStage($event->getStage(), "STARTING"));
        }
    }

    public function finishStage(StageEvent $event)
    {
        if ($this->output->isVerbose()) {
            $this->output->write($this->getMessageForStage($event->getStage(), "FINISHING"));
        }
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
    }

    public function advanceProgress(TaskEvent $event)
    {
        $task = $event->getTask();

        $this->runtimes[$task->getName()] = microtime(true);

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

        if ($this->output->isVeryVerbose()) {
            $start   = $this->runtimes[$task->getName()];
            $message .= sprintf(" (Runtime %0.2fs)", microtime(true) - $start);
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

    protected function getMessageForStage(int $stage, string $status): string
    {
        return PHP_EOL."<options=bold;fg=black;bg=white> >>>>> $status STAGE $stage <<<<< </>";
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
