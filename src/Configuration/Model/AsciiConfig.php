<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

class AsciiConfig
{
    /**
     * @var array|null
     */
    private $asciiConfig;

    public function __construct(?array $asciiConfig)
    {
        $this->asciiConfig = $asciiConfig;
    }

    public function fetchResource(string $resource): ?string
    {
        if (null === $this->asciiConfig) {
            return null;
        }

        $paths = $this->asciiConfig;
        if (!array_key_exists($resource, $paths)) {
            return null;
        }

        // Deal with multiple ascii files by returning one at random.
        if (\is_array($paths[$resource])) {
            shuffle($paths[$resource]);
            return reset($paths[$resource]);
        }

        return $paths[$resource];
    }
}
