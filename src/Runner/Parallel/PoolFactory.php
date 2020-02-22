<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Parallel;

use Amp\Parallel\Worker\DefaultPool;
use Amp\Parallel\Worker\Pool;

class PoolFactory
{
    /**
     * @var array{max_size: int}
     */
    private $config;

    /**
     * @param array{max_size: int} $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(): Pool
    {
        return new DefaultPool(
            (int) ($this->config['max_size'] ?? DefaultPool::DEFAULT_MAX_SIZE)
        );
    }
}
