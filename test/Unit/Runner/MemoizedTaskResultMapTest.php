<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner;

use GrumPHP\Runner\MemoizedTaskResultMap;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MemoizedTaskResultMapTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_does_not_contain_a_task_result(): void
    {
        $map = new MemoizedTaskResultMap();
        self::assertFalse($map->contains('task'));
        self::assertSame(null, $map->get('task'));
    }

    /** @test */
    public function it_contains_a_task_result(): void
    {
        /** @var ObjectProphecy|TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig('task', [], new Metadata([])));
        /** @var ObjectProphecy|ContextInterface $context */
        $context = $this->prophesize(ContextInterface::class);

        $map = new MemoizedTaskResultMap();
        $map->onResult($result = TaskResult::createPassed($task->reveal(), $context->reveal()));

        self::assertTrue($map->contains('task'));
        self::assertSame($result, $map->get('task'));
    }
}
