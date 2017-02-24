<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @property \GrumPHP\Linter\Json\JsonLinter $linter
 */
class JsonLint extends AbstractLinterTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'jsonlint';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = parent::getConfigurableOptions();
        $resolver->setDefaults([
            'detect_key_conflicts' => false,
        ]);

        $resolver->addAllowedTypes('detect_key_conflicts', ['bool']);

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
        $files = $context->getFiles()->name('*.json');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();
        $this->linter->setDetectKeyConflicts($config['detect_key_conflicts']);

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
