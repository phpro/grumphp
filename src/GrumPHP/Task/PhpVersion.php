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
    private $util;

    /**
     * @var GrumPHP
     */
    private $grumPHP;

    /**
     * PhpVersion constructor.
     * @param GrumPHP $grumPHP
     * @param \GrumPHP\Util\PhpVersion $util
     */
    public function __construct(GrumPHP $grumPHP, \GrumPHP\Util\PhpVersion $util)
    {
        $this->grumPHP = $grumPHP;
        $this->util = $util;
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
        if (!$this->util->isSupportedVersion(PHP_VERSION)) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf('PHP version %s is unsupported', PHP_VERSION)
            );
        }

        // Check the project version if defined
        if (array_key_exists('project', $config) !== null) {
            $projectVersion = $config['project'];
            if (!$this->util->isSupportedProjectVersion(PHP_VERSION, $projectVersion)) {
                return TaskResult::createFailed(
                    $this,
                    $context,
                    sprintf('This project requires PHP version %s, you have %s', $projectVersion, PHP_VERSION)
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
