<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php-cs-fixer task v2
 */
class PhpCsFixerV2 extends AbstractPhpCsFixerTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcsfixer2';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'allow_risky' => false,
            'cache_file' => null,
            'config' => null,
            'rules' => array(),
            'using_cache' => true,
            'path_mode' => null,
            'verbose' => true,
        ));

        $resolver->addAllowedTypes('allow_risky', array('bool'));
        $resolver->addAllowedTypes('cache_file', array('null', 'string'));
        $resolver->addAllowedTypes('config', array('null', 'string'));
        $resolver->addAllowedTypes('rules', array('array'));
        $resolver->addAllowedTypes('using_cache', array('bool'));
        $resolver->addAllowedTypes('path_mode', array('null', 'string'));
        $resolver->addAllowedTypes('verbose', array('bool'));

        $resolver->setAllowedValues('path_mode', array(null, 'override', 'intersection'));

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();
        $this->formatter->resetCounter();

        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');
        $arguments->add('--dry-run');
        $arguments->addOptionalArgument('--allow-risky=%s', $config['allow_risky'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--cache-file=%s', $config['cache_file']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalCommaSeparatedArgument('--rules=%s', $config['rules']);
        $arguments->addOptionalArgument('--using-cache', $config['using_cache'] ? 'yes' : 'no');
        $arguments->addOptionalArgument('--path-mode=%s', $config['path_mode']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->add('fix');

        if ($context instanceof RunContext && $config['config'] !== null) {
            return $this->runOnAllFiles($context, $arguments);
        }

        return $this->runOnChangedFiles($context, $arguments, $files);
    }
}
