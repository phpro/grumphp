<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php-cs-fixer task v2
 */
class PhpCsFixerV2 extends AbstractExternalTask
{
    use PhpCsFixerTrait;

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
            'allow_risky' => null,
            'cache_file' => null,
            'config' => null,
            'rules' => null,
            'using_cache' => null,
            'path_mode' => null,
            'verbose' => true,
        ));

        $resolver->addAllowedTypes('allow_risky', array('null', 'string'));
        $resolver->addAllowedTypes('cache_file', array('null', 'string'));
        $resolver->addAllowedTypes('config', array('null', 'string'));
        $resolver->addAllowedTypes('rules', array('null', 'string'));
        $resolver->addAllowedTypes('using_cache', array('null', 'string'));
        $resolver->addAllowedTypes('path_mode', array('null', 'string'));
        $resolver->addAllowedTypes('verbose', array('bool'));

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
        $arguments->addOptionalArgument('--allow-risky=%s', $config['allow_risky']);
        $arguments->addOptionalArgument('--cache-file=%s', $config['cache_file']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--rules=%s', $config['rules']);
        $arguments->addOptionalArgument('--using-cache=%s', $config['using_cache']);
        $arguments->addOptionalArgument('--path-mode=%s', $config['path_mode']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->add('fix');

        if ($context instanceof RunContext && $config['config'] !== null) {
            return $this->runOnAllFiles($context, $arguments);
        }

        return $this->runOnChangedFiles($context, $arguments, $files);
    }
}
