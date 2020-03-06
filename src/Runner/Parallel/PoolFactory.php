<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Parallel;

use Amp\Parallel\Worker\DefaultPool;
use Amp\Parallel\Worker\Pool;

class PoolFactory
{
    /**
     * @var array{max_size: int, enabled: bool}
     */
    private $config;

    /**
     * @param array{max_size: int, enabled: bool} $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function create(): ?Pool
    {
        if (!$this->enabled()) {
            return null;
        }

        return new DefaultPool(
            (int) ($this->config['max_size'] ?? DefaultPool::DEFAULT_MAX_SIZE)
        );
    }
}
