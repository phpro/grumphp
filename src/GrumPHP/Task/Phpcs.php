<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\PhpcsFormatter;
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
     * @var PhpcsFormatter
     */
    protected $formatter;

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
            'encoding' => null,
            'ignore_patterns' => array(),
            'sniffs' => array(),
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('standard', array('null', 'string'));
        $resolver->addAllowedTypes('show_warnings', array('bool'));
        $resolver->addAllowedTypes('tab_width', array('null', 'int'));
        $resolver->addAllowedTypes('encoding', array('null', 'string'));
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
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
        $arguments = $this->addArgumentsFromConfig($arguments, $config);
        $arguments->add('--report-full');
        $arguments->add('--report-json');
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = $this->formatter->format($process);
            if (!$output) {
                $arguments = $this->processBuilder->createArgumentsForCommand('phpcbf');
                $arguments = $this->addArgumentsFromConfig($arguments, $config);
                $output = $this->formatter->formatErrorMessage($arguments, $this->processBuilder);
            }
            return TaskResult::createFailed($this, $context, $output);
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param ProcessArgumentsCollection $arguments
     * @param array $config
     * @return ProcessArgumentsCollection
     */
    protected function addArgumentsFromConfig(ProcessArgumentsCollection $arguments, array $config)
    {
        $arguments->addOptionalArgument('--standard=%s', $config['standard']);
        $arguments->addOptionalArgument('--warning-severity=0', !$config['show_warnings']);
        $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
        $arguments->addOptionalArgument('--encoding=%s', $config['encoding']);
        $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
        return $arguments;
    }
}
