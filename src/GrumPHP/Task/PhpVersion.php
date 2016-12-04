<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpVersion task
 */
class PhpVersion implements TaskInterface
{
    /**
     * @var \GrumPHP\Util\PhpVersion
     */
    private $phpVersionUtility;

    /**
     * @var GrumPHP
     */
    private $grumPHP;

    /**
     * PhpVersion constructor.
     * @param GrumPHP $grumPHP
     * @param \GrumPHP\Util\PhpVersion $phpVersionUtility
     */
    public function __construct(GrumPHP $grumPHP, \GrumPHP\Util\PhpVersion $phpVersionUtility)
    {
        $this->grumPHP = $grumPHP;
        $this->phpVersionUtility = $phpVersionUtility;
    }

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof RunContext;
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context)
    {
        // Check the current version
        $config = $this->getConfiguration();
        if (!$this->phpVersionUtility->isSupportedVersion(PHP_VERSION)) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf('PHP version %s is unsupported', PHP_VERSION)
            );
        }

        // Check the project version if defined
        if ($config['project'] !== null) {
            if (!$this->phpVersionUtility->isSupportedProjectVersion(PHP_VERSION, $config['project'])) {
                return TaskResult::createFailed(
                    $this,
                    $context,
                    sprintf('This project requires PHP version %s, you have %s', $config['project'], PHP_VERSION)
                );
            }
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phpversion';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'project' => null,
            ]
        );
        $resolver->addAllowedTypes('project', ['null', 'string']);

        return $resolver;
    }
}
