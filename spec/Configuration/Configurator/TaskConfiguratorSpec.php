<?php

namespace spec\GrumPHP\Configuration\Configurator;

use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Configuration\Configurator\TaskConfigurator;

class TaskConfiguratorSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TaskConfigurator::class);
    }

    public function it_can_configure_a_task(
        TaskInterface $originalTask,
        TaskInterface $expectedTask,
        TaskConfigInterface $config
    ): void {
        $originalTask->withConfig($config)->will(function ($arguments) use ($expectedTask) {
            $expectedTask->getConfig()->willReturn($arguments[0]);

            return $expectedTask;
        });

        $result = $this->__invoke($originalTask, $config);
        $result->shouldNotBe($originalTask);
        $result->shouldBe($expectedTask);
        $result->getConfig()->shouldBe($config);
    }
}
