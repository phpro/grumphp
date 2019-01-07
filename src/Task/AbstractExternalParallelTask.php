<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;

abstract class AbstractExternalParallelTask extends AbstractExternalTask implements ParallelTaskInterface
{
    /**
     * @return string
     * @throws \ReflectionException
     */
    public static function getStaticName(): string
    {
        $reflector = new \ReflectionClass(static::class);
        /**
         * @var static $instance
         */
        $instance = $reflector->newInstanceWithoutConstructor();
        return $instance->getName();
    }

    /**
     * @return string
     */
    public function getExecutableName(): string
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getExecutablePath(): string
    {
        // TODO: Better add getExecutablePath to ProcessBuilder
        $args = $this->getProcessBuilder()->createArgumentsForCommand($this->getExecutableName());
        return $args->first();
    }

    protected function getProcessBuilder(): ProcessBuilder
    {
        return $this->processBuilder;
    }

    protected function getFormatter(): ProcessFormatterInterface
    {
        return $this->formatter;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        if (!$this->hasWorkToDo($context)) {
            return TaskResult::createSkipped($this, $context);
        }

        $process = $this->resolveProcess($context);
        $process->run();

        return $this->getTaskResult($process, $context);
    }

    /**
     * @param ContextInterface $context
     * @return Process
     */
    public function resolveProcess(ContextInterface $context): Process
    {
        $config  = $this->getConfiguration();
        $process = $this->buildProcess($config, $context);
        return $process;
    }

    /**
     * @param Process $process
     * @param ContextInterface $context
     * @return TaskResult
     */
    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface
    {
        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->getFormatter()->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return parent::getConfiguration();
    }

    public function getStage(): int
    {
        $metadata = $this->grumPHP->getTaskMetadata($this->getName());
        return $metadata['stage'] ?? 0;
    }

    /**
     * This methods specifies if there is work to do for the task.
     * This might be "false" if we use a whitelist/trigger list for the task.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    protected function hasWorkToDo(ContextInterface $context): bool
    {
        return true;
    }

    /**
     * @param  array $config
     * @param ContextInterface $context
     * @return Process
     */
    protected function buildProcess(array $config, ContextInterface $context): Process
    {
        $executable = $this->getExecutableName();
        $arguments  = $this->buildArguments($executable, $config, $context);
        $process    = $this->getProcessBuilder()->buildProcess($arguments);
        return $process;
    }

    /**
     * Override in Task
     *
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    abstract protected function buildArguments(
        string $command,
        array $config,
        ContextInterface $context
    ): ProcessArgumentsCollection;
}
