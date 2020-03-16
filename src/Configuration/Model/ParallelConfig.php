<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

/**
 * @psalm-immutable
 */
class ParallelConfig
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var int
     */
    private $maxSize;

    public function __construct(
        bool $enabled,
        int $maxSize
    ) {
        $this->enabled = $enabled;
        $this->maxSize = $maxSize;
    }

    /**
     * @param array{max_size: int, enabled: bool} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            (bool) ($config['enabled'] ?? false),
            (int) ($config['max_size'] ?? 1)
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }
}
