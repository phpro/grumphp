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

    /**
     * @return string
     */
    public function getExecutableName(): string;

    /**
     * @return string
     */
    public function getExecutablePath(): string;

    /**
     * @param ContextInterface $context
     * @return Process
     */
    public function resolveProcess(ContextInterface $context): Process;

    /**
     * @param Process $process
     * @param ContextInterface $context
     * @return TaskResultInterface
     */
    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface;
}
