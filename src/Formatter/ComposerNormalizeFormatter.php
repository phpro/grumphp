<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessBuilder;
use Symfony\Component\Process\Process;

class ComposerNormalizeFormatter extends RawProcessFormatter
{

    /**
     * @param \GrumPHP\Collection\ProcessArgumentsCollection $defaultArguments
     * @param \GrumPHP\Process\ProcessBuilder $processBuilder
     *
     * @return string
     */
    public function formatErrorMessage(
        ProcessArgumentsCollection $defaultArguments,
        ProcessBuilder $processBuilder
    ): string {
        return sprintf(
            '%sYou can fix some errors by running following command:%s',
            PHP_EOL.PHP_EOL,
            PHP_EOL.$processBuilder->buildProcess($defaultArguments)->getCommandLine()
        );
    }
}
