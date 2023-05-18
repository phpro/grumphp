<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Parallel;

use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerPool;
use GrumPHP\Configuration\Model\ParallelConfig;

class PoolFactory
{
    private ParallelConfig $config;
    private ?WorkerPool $pool = null;

    public function __construct(ParallelConfig $config)
    {
        $this->config = $config;
    }

    public function createShared(): WorkerPool
    {
        if (!$this->pool) {
            $this->pool = new ContextWorkerPool(
                $this->config->getMaxWorkers()
            );
        }

        return $this->pool;
    }
}
