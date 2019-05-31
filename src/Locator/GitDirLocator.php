<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Process\ProcessBuilder;

class GitDirLocator
{
    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    public function __construct(ProcessBuilder $processBuilder)
    {
        $this->processBuilder = $processBuilder;
    }

    public function locate(): string
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('git');
        $arguments->add('rev-parse');
        $arguments->add('--show-toplevel');

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new RuntimeException('The git directory could not be found. Did you initialize git?');
        }

        return $process->getOutput();
    }
}
