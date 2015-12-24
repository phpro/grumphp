<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\Xml\XmlLinter;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class XmlLint
 *
 * @package GrumPHP\Task
 */
class XmlLint extends AbstractLinterTask
{
    /**
     * @var XmlLinter
     */
    protected $linter;

    /**
     * @return string
     */
    public function getName()
    {
        return 'xmllint';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = parent::getConfigurableOptions();
        $resolver->setDefaults(array(
            'load_from_net' => false,
            'x_include' => false,
            'dtd_validation' => false,
            'scheme_validation' => false,
        ));

        $resolver->addAllowedTypes('load_from_net', array('bool'));
        $resolver->addAllowedTypes('x_include', array('bool'));
        $resolver->addAllowedTypes('dtd_validation', array('bool'));
        $resolver->addAllowedTypes('scheme_validation', array('bool'));

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
        $files = $context->getFiles()->name('*.xml');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();

        $this->linter->setLoadFromNet($config['load_from_net']);
        $this->linter->setXInclude($config['x_include']);
        $this->linter->setDtdValidation($config['dtd_validation']);
        $this->linter->setSchemeValidation($config['scheme_validation']);

        $lintErrors = $this->lint($files);
        if ($lintErrors->count()) {
            throw new RuntimeException($lintErrors->__toString());
        }
    }
}
