<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\PhpVersion as PhpVersionUtility;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhpVersion implements TaskInterface
{
    private $phpVersionUtility;
    private $grumPHP;

    public function __construct(GrumPHP $grumPHP, PhpVersionUtility $phpVersionUtility)
    {
        $this->grumPHP = $grumPHP;
        $this->phpVersionUtility = $phpVersionUtility;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();
        if (null === $config['project']) {
            return TaskResult::createPassed($this, $context);
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

    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    public function getName(): string
    {
        return 'phpversion';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'project' => null,
        ]);
        $resolver->addAllowedTypes('project', ['null', 'string']);

        return $resolver;
    }
}
