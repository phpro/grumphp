<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessBuilder;

class ComposerNormalizeFormatter extends RawProcessFormatter
{

  /**
   * @param string $fixerCommand
   *
   * @return string
   */
    public function formatErrorMessage(
        string $fixerCommand
    ): string {
        return sprintf(
            '%sYou can fix some errors by running following command:%s',
            PHP_EOL.PHP_EOL,
            PHP_EOL.$fixerCommand
        );
    }
}
