<?php
namespace GrumPHPE2E;

use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidateArgvPathsTask implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    private $config;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    public function __construct(ProcessBuilder $processBuilder)
    {
        $this->config = new EmptyTaskConfig();
        $this->processBuilder = $processBuilder;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        return ConfigOptionsResolver::fromOptionsResolver(new OptionsResolver());
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return true;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('php');
        $arguments->add(__DIR__.'/validate-argv-paths.php');
        $arguments->addFiles($context->getFiles());

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $process->getOutput().$process->getErrorOutput());
        }

        return TaskResult::createPassed($this, $context);
    }
}
