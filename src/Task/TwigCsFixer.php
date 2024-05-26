<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use Symfony\Component\Process\Process;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class TwigCsFixer extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'paths' => [],
            'level' => null,
            'config' => null,
            'report' => 'text',
            'no-cache' => false,
            'verbose' => false,
            'triggered_by' => ['twig'],
        ]);

        $resolver->addAllowedTypes('paths', ['array']);
        $resolver->addAllowedTypes('level', ['null', 'string']);
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('report', ['null', 'string']);
        $resolver->addAllowedTypes('no-cache', ['bool']);
        $resolver->addAllowedTypes('verbose', ['bool']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $this->isGitContextAllowed($context) || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()
            ->extensions($config['triggered_by'])
            ->paths($config['paths']);

        if (\count($files) === 0) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('twig-cs-fixer');
        $arguments->add('lint');

        if ($this->isGitContextAllowed($context)) {
            $arguments->addFiles($files);
        }

        if ($context instanceof RunContext) {
            $arguments->addArgumentArray('%s', $config['paths']);
        }

        $arguments->addOptionalArgument('--level=%s', $config['level']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--report=%s', $config['report']);

        $arguments->addOptionalArgument('--no-cache', $config['no-cache']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return FixableProcessResultProvider::provide(
                TaskResult::createFailed($this, $context, $this->formatter->format($process)),
                function () use ($arguments): Process {
                    $arguments->add('--fix');
                    return $this->processBuilder->buildProcess($arguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
