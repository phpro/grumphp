<?php

declare(strict_types=1);

namespace GrumPHP\Util;

class ComposerFile
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path, array $configuration)
    {
        $this->path = $path;
        $this->configuration = $configuration;
    }

    public function getBinDir(): string
    {
        $binDir = $this->configuration['config']['bin-dir'] ?? null;

        if (null !== $binDir) {
            return (string) $binDir;
        }

        return 'vendor/bin';
    }

    public function getConfigDefaultPath(): ?string
    {
        return $this->configuration['extra']['grumphp']['config-default-path'] ?? null;
    }

    public function getProjectPath(): ?string
    {
        return $this->configuration['extra']['grumphp']['project-path'] ?? null;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
