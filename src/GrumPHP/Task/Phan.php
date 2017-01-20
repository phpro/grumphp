<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phan task
 */
class Phan extends AbstractExternalTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'phan';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'directory' => null,
            'exclude_file' => '',
            'exclude_directory_list' => [],
            'output_mode' => 'text',
            'output' => null,
            'progress_bar' => true,
            'quick' => false,
            'backward_compatibility_checks' => false,
            'ignore_undeclared' => false,
            'minimum_severity' => 0,
            'parent_constructor_required' => false,
            'dead_code_detection' => false,
            'signature_compatibility' => false,
            'triggered_by' => ['php']
        ]);

        $resolver->addAllowedTypes('directory', ['null', 'array']);
        $resolver->addAllowedTypes('exclude_file', ['string']);
        $resolver->addAllowedTypes('exclude_directory_list', ['array']);
        $resolver->addAllowedTypes('output_mode', ['string']);
        $resolver->addAllowedTypes('output', ['null', 'string']);
        $resolver->addAllowedTypes('progress_bar', ['bool']);
        $resolver->addAllowedTypes('quick', ['bool']);
        $resolver->addAllowedTypes('backward_compatibility_checks', ['bool']);
        $resolver->addAllowedTypes('ignore_undeclared', ['bool']);
        $resolver->addAllowedTypes('minimum_severity', ['int']);
        $resolver->addAllowedTypes('parent_constructor_required', ['bool']);
        $resolver->addAllowedTypes('dead_code_detection', ['bool']);
        $resolver->addAllowedTypes('signature_compatibility', ['bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

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

        $arguments = $this->processBuilder->createArgumentsForCommand('phan');

        $arguments->addSeparatedArgumentArray('--directory', $config['directory']);
        $arguments->addOptionalArgument('--exclude-file', $config['exclude_file']);
        $arguments->addOptionalCommaSeparatedArgument('--exclude_directory_list', $config['exclude_directory_list']);
        $arguments->addOptionalArgument('--output-mode', $config['output_mode']);
        $arguments->addOptionalArgument('--output', $config['output']);
        $arguments->addOptionalArgument('--progress-bar', $config['progress_bar']);
        $arguments->add('--progress-bar');
        $arguments->addOptionalArgument('--quick', $config['quick']);
        $arguments->addOptionalArgument('--backward-compatibility-checks', $config['backward_compatibility_checks']);
        $arguments->addOptionalArgument('--ignore-undeclared', $config['ignore_undeclared']);
        $arguments->addOptionalArgument('--minimum-severity', $config['minimum_severity']);
        $arguments->addOptionalArgument('--parent-constructor-required', $config['parent_constructor_required']);
        $arguments->addOptionalArgument('--dead-code-detection', $config['dead_code_detection']);
        $arguments->addOptionalArgument('--signature-compatibility', $config['signature_compatibility']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
