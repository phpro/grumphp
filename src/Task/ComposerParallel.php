<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SplFileInfo;
use Symfony\Component\Process\Process;

class ComposerParallel extends AbstractExternalParallelTask
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        ProcessFormatterInterface $formatter,
        Filesystem $filesystem
    ) {
        parent::__construct($grumPHP, $processBuilder, $formatter);
        $this->filesystem = $filesystem;
    }

    public function getName(): string
    {
        return 'composer_parallel';
    }

    public function getExecutableName(): string
    {
        return 'composer';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'file'                => './composer.json',
            'no_check_all'        => false,
            'no_check_lock'       => false,
            'no_check_publish'    => false,
            'no_local_repository' => false,
            'with_dependencies'   => false,
            'strict'              => false,
        ]);

        $resolver->addAllowedTypes('file', ['string']);
        $resolver->addAllowedTypes('no_check_all', ['bool']);
        $resolver->addAllowedTypes('no_check_lock', ['bool']);
        $resolver->addAllowedTypes('no_check_publish', ['bool']);
        $resolver->addAllowedTypes('no_local_repository', ['bool']);
        $resolver->addAllowedTypes('with_dependencies', ['bool']);
        $resolver->addAllowedTypes('strict', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * @param Process $process
     * @param ContextInterface $context
     * @return TaskResult
     */
    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface
    {
        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        $config = $this->getConfiguration();
        $files  = $this->getFiles($config, $context);
        if ($config['no_local_repository'] && $this->hasLocalRepository($files->first())) {
            return TaskResult::createFailed($this, $context, 'You have at least one local repository declared.');
        }

        return TaskResult::createPassed($this, $context);
    }

    public function hasWorkToDo(ContextInterface $context): bool
    {
        $config = $this->getConfiguration();

        $files = $this->getFiles($config, $context);

        if (0 === \count($files)) {
            return true;
        }
        return false;
    }

    private function getFiles(array $config, ContextInterface $context)
    {
        $files = $context->getFiles()
            ->path(pathinfo($config['file'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['file'], PATHINFO_BASENAME))
        ;

        return $files;
    }

    /**
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    protected function buildArguments(
        string $command,
        array $config,
        ContextInterface $context
    ): ProcessArgumentsCollection {
        $arguments = $this->processBuilder->createArgumentsForCommand($command);
        $arguments->add('validate');

        $arguments->addOptionalArgument('--no-check-all', $config['no_check_all']);
        $arguments->addOptionalArgument('--no-check-lock', $config['no_check_lock']);
        $arguments->addOptionalArgument('--no-check-publish', $config['no_check_publish']);
        $arguments->addOptionalArgument('--with-dependencies', $config['with_dependencies']);
        $arguments->addOptionalArgument('--strict', $config['strict']);
        $arguments->addOptionalArgument('%s', $config['file']);

        return $arguments;
    }

    private function hasLocalRepository(SplFileInfo $composerFile): bool
    {
        $json    = $this->filesystem->readFromFileInfo($composerFile);
        $package = json_decode($json, true);

        if (!array_key_exists('repositories', $package)) {
            return false;
        }

        foreach ($package['repositories'] as $repository) {
            if ('path' === $repository['type']) {
                return true;
            }
        }

        return false;
    }
}
