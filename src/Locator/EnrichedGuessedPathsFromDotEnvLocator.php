<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Configuration\GuessedPaths;

class EnrichedGuessedPathsFromDotEnvLocator
{
    public function locate(GuessedPaths $guessedPaths): GuessedPaths
    {
        return $guessedPaths;
    }
}
