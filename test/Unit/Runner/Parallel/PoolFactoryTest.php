<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Parallel;

use Amp\Parallel\Worker\Pool;
use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use PHPUnit\Framework\TestCase;

class PoolFactoryTest extends TestCase
{
    /** @test */
    public function it_can_create_pool(): void
    {
        $config = new ParallelConfig($enabled = true, $maxSize = 10);
        $pool = (new PoolFactory($config))->create();

        self::assertInstanceOf(Pool::class, $pool);
        self::assertSame($maxSize, $pool->getMaxSize());
    }
}
