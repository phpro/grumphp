<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\GitPrePushContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SplFileInfo;

/**
 * Composer task
 */
class Composer extends AbstractExternalTask
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Composer constructor.
     *
     * @param GrumPHP                   $grumPHP
     * @param ProcessBuilder            $processBuilder
     * @param ProcessFormatterInterface $formatter
     * @param Filesystem                $filesystem
     */
    public function __construct(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        ProcessFormatterInterface $formatter,
        Filesystem $filesystem
    ) {
        parent::__construct($grumPHP, $processBuilder, $formatter);
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'composer';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'file' => './composer.json',
            'no_check_all' => false,
            'no_check_lock' => false,
            'no_check_publish'  => false,
            'no_local_repository' => false,
            'with_dependencies' => false,
            'strict' => false
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

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext 
                || $context instanceof GitPrePushContext 
                || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()
            ->path(pathinfo($config['file'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['file'], PATHINFO_BASENAME));
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer');
        $arguments->add('validate');

        $arguments->addOptionalArgument('--no-check-all', $config['no_check_all']);
        $arguments->addOptionalArgument('--no-check-lock', $config['no_check_lock']);
        $arguments->addOptionalArgument('--no-check-publish', $config['no_check_publish']);
        $arguments->addOptionalArgument('--with-dependencies', $config['with_dependencies']);
        $arguments->addOptionalArgument('--strict', $config['strict']);
        $arguments->addOptionalArgument('%s', $config['file']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        if ($config['no_local_repository'] && $this->hasLocalRepository($files->first())) {
            return TaskResult::createFailed($this, $context, 'You have at least one local repository declared.');
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * Checks if composer.local host one or more local repositories.
     *
     * @param SplFileInfo $composerFile
     *
     * @return bool
     */
    private function hasLocalRepository(SplFileInfo $composerFile)
    {
        $json = $this->filesystem->readFromFileInfo($composerFile);
        $package = json_decode($json, true);

        foreach ($package['repositories'] as $repository) {
            if ($repository['type'] === 'path') {
                return true;
            }
        }

        return false;
    }
}
