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
            'whitelist_path_pattern' => null,
            'ignore_patterns' => array(),
            'sniffs' => array(),
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('standard', array('null', 'string'));
        $resolver->addAllowedTypes('show_warnings', array('bool'));
        $resolver->addAllowedTypes('tab_width', array('null', 'int'));
        $resolver->addAllowedTypes('whitelist_path_pattern', array('null', 'string'));
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
        $config = $this->getConfiguration();

        /** @var string|null $whitelistPathPattern */
        $whitelistPathPattern = $this->getWhitelistPathPattern($config);
        if (!$whitelistPathPattern) {
            $files = $context->getFiles()->extensions($config['triggered_by']);
        } else {
            $files = $context->getFiles()->path($whitelistPathPattern);
        }

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

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
     * @param array $config
     *
     * @return null|string
     */
    protected function getWhitelistPathPattern(array $config)
    {
        /** @var string|null $whitelistPathPattern */
        $whitelistPathPattern = $config['whitelist_path_pattern'];
        if (!$whitelistPathPattern) {
            return null;
        }

        $whitelistPathPattern = $this->escapePatternDirectorySeparator($whitelistPathPattern);
        $whitelistPathPattern = '/'.$whitelistPathPattern.'.(%s)$/';
        $whitelistPathPattern = sprintf($whitelistPathPattern, implode('|', $config['triggered_by']));

        return $whitelistPathPattern;
    }

    /**
     * @param string $pattern
     *
     * @return string
     */
    protected function escapePatternDirectorySeparator($pattern)
    {
        return str_replace('/', '\\/', $pattern);
    }
}
