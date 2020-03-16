<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

/**
 * @psalm-immutable
 */
class FixerConfig
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var bool
     */
    private $fixByDefault;

    public function __construct(
        bool $enabled,
        bool $fixByDefault
    ) {
        $this->enabled = $enabled;
        $this->fixByDefault = $fixByDefault;
    }

    /**
     * @param array{fix_by_default: bool, enabled: bool} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            (bool) ($config['enabled'] ?? false),
            (bool) ($config['fix_by_default'] ?? false)
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function fixByDefault(): bool
    {
        return $this->fixByDefault;
    }
}
