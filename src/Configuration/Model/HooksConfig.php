<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

/**
 * @psalm-immutable
 */
class HooksConfig
{
    /**
     * @var string|null
     */
    private $dir;

    /**
     * @var string
     */
    private $preset;

    /**
     * @var array
     */
    private $variables;

    public function __construct(
        ?string $dir,
        string $preset,
        array $variables
    ) {
        $this->dir = $dir;
        $this->preset = $preset;
        $this->variables = $variables;
    }

    public function getDir(): ?string
    {
        return $this->dir;
    }

    public function getPreset(): string
    {
        return $this->preset;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
