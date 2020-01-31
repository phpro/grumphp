<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\PhpVersion as PhpVersionUtility;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhpVersion implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    private $config;

    /**
     * @var PhpVersionUtility
     */
    private $phpVersionUtility;

    public function __construct(PhpVersionUtility $phpVersionUtility)
    {
        $this->config = new EmptyTaskConfig();
        $this->phpVersionUtility = $phpVersionUtility;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        if (null === $config['project']) {
            return TaskResult::createSkipped($this, $context);
        }

        // Check the current version
        if (!$this->phpVersionUtility->isSupportedVersion(PHP_VERSION)) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf('PHP version %s is unsupported', PHP_VERSION)
            );
        }

        // Check the project version if defined
        if (!$this->phpVersionUtility->isSupportedProjectVersion(PHP_VERSION, $config['project'])) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf('This project requires PHP version %s, you have %s', $config['project'], PHP_VERSION)
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'project' => null,
        ]);
        $resolver->addAllowedTypes('project', ['null', 'string']);

        return $resolver;
    }
}
