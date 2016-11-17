<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpVersion task
 */
class PhpVersion implements TaskInterface
{

    /**
     * @var array
     */
    private $versions;

    /**
     * @var string
     */
    private $projectVersion;

    /**
     * PhpVersion constructor.
     * @param GrumPHP $config
     * @param array $versions
     */
    public function __construct(GrumPHP $config, array $versions)
    {
        $this->versions = $versions;
        $options = $config->getTaskConfiguration($this->getName());
        if (array_key_exists('project', $options)) {
            $this->projectVersion = $options['project'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phpversion';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return [];
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

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return true;
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context)
    {
        $versionIsSupported = false;
        $now = new \DateTime();
        foreach ($this->versions as $number => $eol) {
            $eol = new \DateTime($eol);
            if ($now < $eol && version_compare(PHP_VERSION, $number) >= 0) {
                $versionIsSupported = true;
            }
        }
        if (!$versionIsSupported) {
            return TaskResult::createFailed($this, $context, sprintf('PHP version %s is end of life', PHP_VERSION));
        }
        if ($this->projectVersion !== null && version_compare(PHP_VERSION, $this->projectVersion) === -1) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf('This project requires PHP version %s, you have %s', $this->projectVersion, PHP_VERSION)
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
