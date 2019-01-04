<?php

namespace GrumPHP\Task\Traits;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Process\Process;

trait FiltersFilesTrait
{
    /**
     * @param $config
     * @param ContextInterface $context
     * @return FilesCollection
     */
    protected function getFilteredFiles($config, ContextInterface $context): FilesCollection
    {
        $whitelistPatterns = $config['whitelist_patterns'] ?? [];
        $extensions        = $config['triggered_by'] ?? [];
        $ignorePatterns    = $config['ignore_patterns'] ?? [];
        $forcePatterns     = $config['force_patterns'] ?? [];

        $files = $context->getFiles();

        if (count($whitelistPatterns) !== 0) {
            $files = $files->paths($whitelistPatterns);
        }
        if (count($ignorePatterns) !== 0) {
            $files = $files->notPaths($ignorePatterns);
        }
        if (count($extensions) !== 0) {
            $files = $files->extensions($extensions);
        }

        // Overrides all previous rules
        if (count($forcePatterns) !== 0) {
            $forcedFiles = $context->getFiles()->paths($forcePatterns);
            $files = $files->ensureFiles($forcedFiles);
        }

        return $files;
    }
}
