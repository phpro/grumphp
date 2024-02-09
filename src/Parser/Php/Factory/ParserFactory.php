<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Factory;

use PhpParser\ParserFactory as PhpParserFactory;
use PhpParser\PhpVersion;

class ParserFactory
{
    public function createFromOptions(array $options): \PhpParser\Parser
    {
        $version = $options['php_version'] ?? null;

        return (new PhpParserFactory())->createForVersion(
            match ($version) {
                null => PhpVersion::getHostVersion(),
                'latest' => PhpVersion::getNewestSupported(),
                default => PhpVersion::fromString($version)
            }
        );
    }
}
