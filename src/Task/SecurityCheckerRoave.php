<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityCheckerRoave extends AbstractExternalTask
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(
        ProcessBuilder $processBuilder,
        ProcessFormatterInterface $formatter,
        Filesystem $filesystem
    ) {
        parent::__construct($processBuilder, $formatter);
        $this->filesystem = $filesystem;
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'jsonfile' => './composer.json',
            'lockfile' => './composer.lock',
            'run_always' => false,
        ]);

        $resolver->addAllowedTypes('jsonfile', ['string']);
        $resolver->addAllowedTypes('lockfile', ['string']);
        $resolver->addAllowedTypes('run_always', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {

        $config = $this->getConfig()->getOptions();
        $composerFile = $config['jsonfile'];

        if (!$this->filesystem->isFile($composerFile)) {
             return TaskResult::createSkipped($this, $context);
        }

        if (!$this->hasRoaveSecurityAdvisoriesInstalled($composerFile)) {
            return TaskResult::createFailed(
                $this,
                $context,
                'This task is only available when roave/security-advisories is installed as a library.'
            );
        }

        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()
            ->path(pathinfo($config['lockfile'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['lockfile'], PATHINFO_BASENAME));
        if (0 === \count($files) && !$config['run_always']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer');
        $arguments->add('update');
        $arguments->add('--dry-run');
        $arguments->add('roave/security-advisories');

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }

    private function hasRoaveSecurityAdvisoriesInstalled(string $composerFile): bool
    {
        $json = $this->filesystem->readPath($composerFile);
        try {
            $package = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return false;
        }

        if (array_key_exists('require', $package)
            && array_key_exists('roave/security-advisories', $package['require'])) {
            return true;
        }

        if (array_key_exists('require-dev', $package)
            && array_key_exists('roave/security-advisories', $package['require-dev'])) {
            return true;
        }

        return false;
    }
}
