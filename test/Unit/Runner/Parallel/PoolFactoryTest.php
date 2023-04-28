<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Parallel;

use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerPool;
use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use PHPUnit\Framework\TestCase;

class PoolFactoryTest extends TestCase
{
    /** @test */
    public function it_can_create_pool(): void
    {
        $config = new ParallelConfig($enabled = true, $maxSize = 10);
        $factory = new PoolFactory($config);
        $pool1 = $factory->createShared();
        $pool2 = $factory->createShared();

        self::assertInstanceOf(ContextWorkerPool::class, $pool1);
        self::assertInstanceOf(ContextWorkerPool::class, $pool2);
        self::assertSame($maxSize, $pool1->getLimit());
        self::assertSame($pool1, $pool2);
    }
}
