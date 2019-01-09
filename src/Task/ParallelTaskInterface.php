<?php

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;

interface ParallelTaskInterface extends TaskInterface
{
    /**
     * TODO: Helper - I think that getName() should actually be static.
     *
     * @return string
     * @throws \ReflectionException
     */
    public static function getStaticName() : string;

    public function getExecutableName(): string;

    public function getExecutablePath(): string;

    public function resolveProcess(ContextInterface $context, string $passthru = ""): Process;

    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface;

    /**
     * Determines if the task hsa any work to do for the given $context.
     *
     * @param ContextInterface $context
     * @return bool
     */
    public function hasWorkToDo(ContextInterface $context): bool;

    /**
     * Defines the "stage" on which the task should run
     *
     * @return int
     */
    public function getStage(): int;

    /**
     * Allows to provide arbitrary arguments/options for the command
     *
     * @return string
     */
    public function getPassthru(): string;
}
