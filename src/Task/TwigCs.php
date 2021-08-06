<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwigCs extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'path' => '.',
            'severity' => 'warning',
            'display' => 'all',
            'ruleset' => 'FriendsOfTwig\Twigcs\Ruleset\Official',
            'triggered_by' => ['twig'],
            'exclude' => [],
        ]);

        $resolver->addAllowedTypes('path', ['string']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('severity', ['string']);
        $resolver->addAllowedTypes('display', ['string']);
        $resolver->addAllowedTypes('ruleset', ['string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('twigcs');
        $arguments->add($config['path']);

        $arguments->addOptionalArgument('--severity=%s', $config['severity']);
        $arguments->addOptionalArgument('--display=%s', $config['display']);
        $arguments->addOptionalArgument('--ruleset=%s', $config['ruleset']);
        $arguments->addOptionalArgument('--ansi', true);

        // Get a list of all changed files, and prepare them for comparison.
        $changedFiles = array_map(function($item) {
            return preg_quote($item->getPathName(), '/');
        }, $files->toArray());

        // Regexp for exclude config.
        $excludePattern = '/('. implode('|', $config['exclude']) .')/';
        // Regexp for triggered_by config.
        $extensionPattern = '/^(.*?)\.('. implode('|', $config['triggered_by']) .')$/';
        // Regexp for all changed files.
        $changedFilesPattern = '/('. implode('|', $changedFiles) .')$/';

        // Scans entire current directory.
        $projectFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator('.'));
        foreach($projectFiles as $projectFile) {
            // Cleanup the pathName string.
            $pathName = str_replace('./', '', $projectFile->getPathName());
            // Skip files that are meant to be excluded.
            if(preg_match($excludePattern, $pathName) === 1) continue;
            // Skip files that do not match the triggered_by config.
            if(preg_match($extensionPattern, $pathName) === 0) continue;
            // Add files that have not been changed to the exclude config.
            if(preg_match($changedFilesPattern, $pathName) === 0) {
                $config['exclude'][] = $pathName;
            }
        }

        // removes all NULL, FALSE and Empty Strings
        $exclude = array_filter($config['exclude'], 'strlen');
        $arguments->addArgumentArray('--exclude=%s', $exclude);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
