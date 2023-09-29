<?php

namespace GrumPHP\IO;

use OndraM\CiDetector\Ci\GitHubActions;
use OndraM\CiDetector\CiDetector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IOFactory
{
    public function __construct(private CiDetector $ciDetector)
    {
    }

    public function create(InputInterface $input, OutputInterface $output): IOInterface
    {
        if ($this->ciDetector->isCiDetected()) {
            $platform = $this->ciDetector->detect();

            if ($platform instanceof GitHubActions) {
                return new GitHubActionsIO($input, $output);
            }
        }

        return new ConsoleIO($input, $output);
    }
}
