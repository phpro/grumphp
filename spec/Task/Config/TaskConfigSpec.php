<?php

namespace spec\GrumPHP\Task\Config;

use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfigInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Task\Config\TaskConfig;

class TaskConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'name',
            ['option' => 'value'],
            new Metadata(['task' => 'taskName'])
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TaskConfig::class);
    }

    public function it_is_a_task_config(): void
    {
        $this->shouldImplement(TaskConfigInterface::class);
    }

    public function it_contains_name(): void
    {
        $this->getName()->shouldBe('name');
    }

    public function it_contains_options(): void
    {
        $this->getOptions()->shouldBe(['option' => 'value']);
    }

    public function it_contains_default_meta(): void
    {
        $this->getMetadata()->toArray()['task']->shouldBe('taskName');
    }
}
