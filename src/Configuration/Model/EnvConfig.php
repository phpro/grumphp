<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

/**
 * @psalm-immutable
 */
class EnvConfig
{
    /**
     * @var list<string>
     */
    private $files;

    /**
     * @var array<string, string>
     */
    private $variables;

    /**
     * @var list<string>
     */
    private $paths;

    /**
     * @param list<string> $files
     * @param array<string, string> $variables
     * @param list<string> $paths
     */
    public function __construct(
        array $files,
        array $variables,
        array $paths
    ) {
        $this->files = $files;
        $this->variables = $variables;
        $this->paths = $paths;
    }

    /**
     * @param array{max_workers: int, enabled: bool} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            (array) ($config['files'] ?? []),
            (array) ($config['variables'] ?? []),
            (array) ($config['paths'] ?? [])
        );
    }

    /**
     * @return list<string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function hasFiles(): bool
    {
        return (bool) count($this->files);
    }

    /**
     * @return array<string, string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function hasVariables(): bool
    {
        return (bool) count($this->variables);
    }

    /**
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function hasPaths(): bool
    {
        return (bool) count($this->paths);
    }
}
