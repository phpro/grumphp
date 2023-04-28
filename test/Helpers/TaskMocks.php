<?php
declare(strict_types=1);

namespace GrumPHPTest\Helpers;

use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\TaskInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

trait TaskMocks
{
    use ProphecyTrait;

    protected function mockTask(string $name = 'task', array $meta = []): TaskInterface
    {
        /** @var ObjectProphecy|TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig($name, [], new Metadata($meta)));

        return $task->reveal();
    }
}
