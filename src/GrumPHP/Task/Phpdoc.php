<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SimpleXMLElement;

/**
 * Phpdoc task
 */
class Phpdoc extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpdoc';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
            'config_file' => null,
            'target_folder' => null,
            'cache_folder' => null,
            'filename' => null,
            'directory' => null,
            'encoding' => null,
            'extensions' => null,
            'ignore' => null,
            'ignore_tags' => null,
            'ignore_symlinks' => null,
            'markers' => null,
            'title' => null,
            'force' => null,
            'visibility' => null,
            'default_package_name' => null,
            'source_code' => null,
            'progress_bar' => null,
            'template' => null,
            'quiet' => null,
            'ansi' => null,
            'no_ansi' => null,
            'no_interaction' => null]
        );

        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('target_folder', ['null', 'string']);
        $resolver->addAllowedTypes('cache_folder', ['null', 'string']);
        $resolver->addAllowedTypes('filename', ['null', 'string']);
        $resolver->addAllowedTypes('directory', ['null', 'string']);
        $resolver->addAllowedTypes('encoding', ['null', 'string']);
        $resolver->addAllowedTypes('extensions', ['null', 'string']);
        $resolver->addAllowedTypes('ignore', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_tags', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_symlinks', ['null', 'string']);
        $resolver->addAllowedTypes('markers', ['null', 'string']);
        $resolver->addAllowedTypes('title', ['null', 'string']);
        $resolver->addAllowedTypes('force', ['null', 'bool']);
        $resolver->addAllowedTypes('visibility', ['null', 'string']);
        $resolver->addAllowedTypes('default_package_name', ['null', 'string']);
        $resolver->addAllowedTypes('source_code', ['null', 'bool']);
        $resolver->addAllowedTypes('progress_bar', ['null', 'bool']);
        $resolver->addAllowedTypes('template', ['null', 'string']);
        $resolver->addAllowedTypes('quiet', ['null', 'bool']);
        $resolver->addAllowedTypes('ansi', ['null', 'bool']);
        $resolver->addAllowedTypes('no_ansi', ['null', 'bool']);
        $resolver->addAllowedTypes('no_interaction', ['null', 'bool']);

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
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpdoc');
        $arguments->addOptionalArgumentWithSeparatedValue('--config', $config['config_file']);
        $arguments->addOptionalArgumentWithSeparatedValue('--target', $config['target_folder']);
        $arguments->addOptionalArgumentWithSeparatedValue('--cache-folder', $config['cache_folder']);
        $arguments->addOptionalArgumentWithSeparatedValue('--filename', $config['filename']);
        $arguments->addOptionalArgumentWithSeparatedValue('--directory', $config['directory']);
        $arguments->addOptionalArgumentWithSeparatedValue('--encoding', $config['encoding']);
        $arguments->addOptionalArgumentWithSeparatedValue('--extensions', $config['extensions']);
        $arguments->addOptionalArgumentWithSeparatedValue('--ignore', $config['ignore']);
        $arguments->addOptionalArgumentWithSeparatedValue('--ignore-tags', $config['ignore_tags']);
        $arguments->addOptionalArgument('--ignore-symlinks', $config['ignore_symlinks']);
        $arguments->addOptionalArgumentWithSeparatedValue('--markers', $config['markers']);
        $arguments->addOptionalArgumentWithSeparatedValue('--title', $config['title']);
        $arguments->addOptionalArgument('--force', $config['force']);
        $arguments->addOptionalArgumentWithSeparatedValue('--visibility', $config['visibility']);
        $arguments->addOptionalArgumentWithSeparatedValue('--defaultpackagename', $config['default_package_name']);
        $arguments->addOptionalArgument('--sourcecode', $config['source_code']);
        $arguments->addOptionalArgument('--progressbar', $config['progress_bar']);
        $arguments->addOptionalArgumentWithSeparatedValue('--template', $config['template']);
        $arguments->addOptionalArgument('--quiet', $config['progress_bar']);
        $arguments->addOptionalArgument('--ansi', $config['progress_bar']);
        $arguments->addOptionalArgument('--no-ansi', $config['progress_bar']);
        $arguments->addOptionalArgument('--no-interaction', $config['progress_bar']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        $trueTargetFolder = null;

        if ($process->isSuccessful() && $context instanceof GitPreCommitContext) {
            if (file_exists('phpdoc.dist.xml')) {
                $xmlElement = new SimpleXMLElement(file_get_contents('phpdoc.dist.xml'));
                $trueTargetFolder = $xmlElement->transformer->target;
            }

            if (file_exists('phpdoc.xml')) {
                $xmlElement = new SimpleXMLElement(file_get_contents('phpdoc.xml'));
                $trueTargetFolder = $xmlElement->transformer->target;
            }

            if ($config['config_file'] && file_exists($config['config_file'])) {
                $xmlElement = new SimpleXMLElement(file_get_contents($config['config_file']));
                $trueTargetFolder = $xmlElement->transformer->target;
            }

            if (($config['target_folder'])) {
                $trueTargetFolder = $config['target_folder'];
            }

            if (!file_exists('phpdoc.dist.xml')
                && !file_exists('phpdoc.xml')
                && !$config['config_file']
                && !$config['target_folder']
            ) {
                 $trueTargetFolder = 'output';
            }

            $argumentsGit = $this->processBuilder->createArgumentsForCommand('git');
            $argumentsGit->addOptionalArgumentWithSeparatedValue('add', $trueTargetFolder . '*');

            $processGit = $this->processBuilder->buildProcess($argumentsGit);
            $processGit->run();

            if (!$processGit->isSuccessful()) {
                return TaskResult::createFailed($this, $context, $this->formatter->format($processGit));
            }
        }

        return TaskResult::createPassed($this, $context);
    }
}
