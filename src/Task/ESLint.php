<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class ESLint extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            // Task config options
            'bin' => null,
            'triggered_by' => ['js', 'jsx', 'ts', 'tsx', 'vue'],
            'whitelist_patterns' => null,
            
            // ESLint native config options
            'config' => null,
            'debug' => false,
            'format' => null,
            'max_warnings' => null,
            'no_eslintrc' => false,
            'quiet' => false,
        ]);

        // Task config options
        $resolver->addAllowedTypes('bin', ['null', 'string']);
        $resolver->addAllowedTypes('whitelist_patterns', ['null', 'array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        
        // ESLint native config options
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('debug', ['bool']);
        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('max_warnings', ['null', 'integer']);
        $resolver->addAllowedTypes('no_eslintrc', ['bool']);
        $resolver->addAllowedTypes('quiet', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context
            ->getFiles()
            ->paths($config['whitelist_patterns'] ?? [])
            ->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = isset($config['bin'])
            ? ProcessArgumentsCollection::forExecutable($config['bin'])
            : $this->processBuilder->createArgumentsForCommand('eslint');

        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--debug', $config['debug']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--no-eslintrc', $config['no_eslintrc']);
        $arguments->addOptionalArgument('--quiet', $config['quiet']);
        $arguments->addOptionalIntegerArgument('--max-warnings=%d', $config['max_warnings']);
        $arguments->addFiles($files);

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
