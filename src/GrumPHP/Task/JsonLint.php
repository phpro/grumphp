<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class JsonLint
 *
 * @package GrumPHP\Task
 */
class JsonLint extends AbstractLinterTask
{
    /**
     * @var JsonLinter
     */
    protected $linter;

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
        $resolver->setDefaults(array(
            'detect_key_conflicts' => false,
        ));

        $resolver->addAllowedTypes('detect_key_conflicts', array('bool'));

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
            return;
        }

        $config = $this->getConfiguration();

        $this->linter->setDetectKeyConflicts($config['detect_key_conflicts']);

        $lintErrors = $this->lint($files);
        if ($lintErrors->count()) {
            throw new RuntimeException($lintErrors->__toString());
        }
    }
}
