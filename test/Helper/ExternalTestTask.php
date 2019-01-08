<?php /** @noinspection PhpMissingParentConstructorInspection */

namespace GrumPHPTest\Helper;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;

class ExternalTestTask extends AbstractExternalTask implements TestTaskInterface
{
    use ExternalTestTaskTrait;

    public function run(ContextInterface $context): TaskResultInterface
    {
        $process = $this->resolveProcess($context);
        $process->run();
        return $this->getTaskResult($process, $context);
    }

    /**
     * @param Process $process
     * @param ContextInterface $context
     * @return TaskResult
     */
    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface
    {
        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
