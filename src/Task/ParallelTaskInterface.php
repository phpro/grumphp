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

    public function resolveProcess(ContextInterface $context): Process;

    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface;

    public function getStage(): int;
}
