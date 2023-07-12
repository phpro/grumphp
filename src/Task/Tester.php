<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class Tester extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'path' => '.',
            'always_execute' => false,
            'log' => null,
            'show_information_about_skipped_tests' => false,
            'stop_on_fail' => false,
            'parallel_processes' => null,
            'output' => null,
            'temp' => null,
            'setup' => null,
            'colors' => null,
            'coverage' => null,
            'coverage_src' => null,
            'php_ini_configuration_path' => null,
            'default_php_ini_configuration' => false,
        ]);

        $resolver->addAllowedTypes('path', ['string']);
        $resolver->addAllowedTypes('always_execute', ['bool']);
        $resolver->addAllowedTypes('log', ['null', 'string']);
        $resolver->addAllowedTypes('show_information_about_skipped_tests', ['bool']);
        $resolver->addAllowedTypes('stop_on_fail', ['bool']);
        $resolver->addAllowedTypes('parallel_processes', ['null', 'int']);
        $resolver->addAllowedTypes('output', ['null', 'string']);
        $resolver->addAllowedTypes('temp', ['null', 'string']);
        $resolver->addAllowedTypes('setup', ['null', 'string']);
        $resolver->addAllowedTypes('colors', ['null', 'int']);
        $resolver->addAllowedTypes('coverage', ['null', 'string']);
        $resolver->addAllowedTypes('coverage_src', ['null', 'string']);
        $resolver->addAllowedTypes('php_ini_configuration_path', ['null', 'string']);
        $resolver->addAllowedTypes('default_php_ini_configuration', ['bool']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->names(['*Test.php', '*.phpt']);
        if (0 === \count($files) && !$config['always_execute']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('tester');
        $arguments->add($config['path']);
        $arguments->addOptionalArgumentWithSeparatedValue('--log', $config['log']);
        $arguments->addOptionalArgument('-s', $config['show_information_about_skipped_tests']);
        $arguments->addOptionalArgument('--stop-on-fail', $config['stop_on_fail']);
        $arguments->addOptionalIntegerArgument('-j', $config['parallel_processes']);
        $arguments->addOptionalIntegerArgument('%s', $config['parallel_processes']);
        $arguments->addOptionalArgumentWithSeparatedValue('-o', $config['output']);
        $arguments->addOptionalArgumentWithSeparatedValue('--temp', $config['temp']);
        $arguments->addOptionalArgumentWithSeparatedValue('--setup', $config['setup']);
        $arguments->addOptionalIntegerArgument('--colors', $config['colors']);
        $arguments->addOptionalIntegerArgument('%s', $config['colors']);
        $arguments->addOptionalArgumentWithSeparatedValue('--coverage', $config['coverage']);
        $arguments->addOptionalArgumentWithSeparatedValue('--coverage-src', $config['coverage_src']);
        $arguments->addOptionalArgumentWithSeparatedValue('-c', $config['php_ini_configuration_path']);
        $arguments->addOptionalArgument('-C', $config['default_php_ini_configuration']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
