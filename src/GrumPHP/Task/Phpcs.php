<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phpcs task
 */
class Phpcs extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcs';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'standard' => null,
            'show_warnings' => true,
            'tab_width' => null,
            'whitelist_patterns' => array(),
            'ignore_patterns' => array(),
            'sniffs' => array(),
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('standard', array('null', 'string'));
        $resolver->addAllowedTypes('show_warnings', array('bool'));
        $resolver->addAllowedTypes('tab_width', array('null', 'int'));
        $resolver->addAllowedTypes('whitelist_patterns', array('array'));
        $resolver->addAllowedTypes('ignore_patterns', array('array'));
        $resolver->addAllowedTypes('sniffs', array('array'));
        $resolver->addAllowedTypes('triggered_by', array('array'));

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        /** @var array $config */
        $config = $this->getConfiguration();
        /** @var array $whitelistPatterns */
        $whitelistPatterns = $config['whitelist_patterns'];
        /** @var array $extensions */
        $extensions = $config['triggered_by'];

        if (0 === count($whitelistPatterns)) {
            $files = $context->getFiles()->extensions($extensions);
        } else {
            array_walk($whitelistPatterns, array($this, 'escapePatternDirectorySeparator'), $extensions);
            $files = $context->getFiles()->paths($whitelistPatterns);
        }

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
        $arguments->addOptionalArgument('--standard=%s', $config['standard']);
        $arguments->addOptionalArgument('--warning-severity=0', !$config['show_warnings']);
        $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
        $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param string $pattern
     * @param int $index
     * @param array $extensions
     */
    protected function escapePatternDirectorySeparator(&$pattern, $index, $extensions)
    {
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = '/'.$pattern.'(%s)$/i';
        $pattern = sprintf($pattern, implode('|', $extensions));
    }
}
