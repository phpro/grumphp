<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
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

    protected function getGrumPHP(): GrumPHP
    {
        return $this->grumPHP;
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

    public function resolveProcess(ContextInterface $context, string $passthru = ""): Process
    {
        $config = $this->getConfiguration();
        if (empty($passthru)) {
            $passthru = $this->getPassthru();
        }
        $process = $this->buildProcess($config, $context, $passthru);
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
        $metadata = $this->getGrumPHP()->getTaskMetadata($this->getName());
        return $metadata['stage'] ?? 0;
    }

    public function getPassthru(): string
    {
        $metadata = $this->getGrumPHP()->getTaskMetadata($this->getName());
        return $metadata['passthru'] ?? "";
    }

    /**
     * This methods specifies if there is work to do for the task.
     * This might be "false" if we use a whitelist/trigger list for the task.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function hasWorkToDo(ContextInterface $context): bool
    {
        return true;
    }

    protected function buildProcess(array $config, ContextInterface $context, string $passthru = ""): Process
    {
        $executable = $this->getExecutableName();
        $arguments  = $this->buildArguments($executable, $config, $context);
        $process    = $this->getProcessBuilder()->buildProcess($arguments, $passthru);
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
