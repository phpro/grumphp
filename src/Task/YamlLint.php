<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @property \GrumPHP\Linter\Yaml\YamlLinter $linter
 */
class YamlLint extends AbstractLinterTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'yamllint';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = parent::getConfigurableOptions();
        $resolver->setDefaults([
            'object_support' => false,
            'exception_on_invalid_type' => false,
            'parse_constant' => false,
            'parse_custom_tags' => false,
        ]);

        $resolver->addAllowedTypes('object_support', ['bool']);
        $resolver->addAllowedTypes('exception_on_invalid_type', ['bool']);
        $resolver->addAllowedTypes('parse_constant', ['bool']);
        $resolver->addAllowedTypes('parse_custom_tags', ['bool']);

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
        $files = $context->getFiles()->name('/\.(yaml|yml)$/i');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();
        $this->linter->setObjectSupport($config['object_support']);
        $this->linter->setExceptionOnInvalidType($config['exception_on_invalid_type']);
        $this->linter->setParseCustomTags($config['parse_custom_tags']);
        $this->linter->setParseConstants($config['parse_constant']);

        try {
            $lintErrors = $this->lint($files);
        } catch (RuntimeException $e) {
            return TaskResult::createFailed($this, $context, $e->getMessage());
        }

        if ($lintErrors->count()) {
            return TaskResult::createFailed($this, $context, (string) $lintErrors);
        }

        return TaskResult::createPassed($this, $context);
    }
}
