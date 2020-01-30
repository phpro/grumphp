<?php

namespace spec\GrumPHP\Task\Config;

use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfigInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Task\Config\EmptyTaskConfig;

class EmptyTaskConfigSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EmptyTaskConfig::class);
    }

    public function it_is_a_task_config(): void
    {
        $this->shouldImplement(TaskConfigInterface::class);
    }

    public function it_contains_empty_name(): void
    {
        $this->getName()->shouldBe('');
    }

    public function it_contains_empty_options(): void
    {
        $this->getOptions()->shouldBe([]);
    }

    public function it_contains_default_meta(): void
    {
        $this->getMetadata()->toArray()->shouldBe(
            Metadata::getConfigurableOptions()->resolve([])
        );
    }
}
