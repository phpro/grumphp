<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

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
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'standard' => 'PSR2',
            'show_warnings' => true,
            'tab_width' => null,
            'ignore_patterns' => array(),
            'sniffs' => array(),
        );
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
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
        $arguments->addRequiredArgument('--standard=%s', $config['standard']);
        $arguments->addOptionalArgument('--warning-severity=0', !$config['show_warnings']);
        $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
        $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
