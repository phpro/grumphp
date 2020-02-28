<?php

namespace spec\GrumPHP\Task\Config;

use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfigInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Task\Config\LazyTaskConfig;

class LazyTaskConfigSpec extends ObjectBehavior
{
    public function let(TaskConfigInterface $config): void
    {
        $isCalled = false;
        $this->beConstructedWith(static function () use ($config, &$isCalled) {
            if ($isCalled) {
                throw new \RuntimeException('Proxy should only be called once!');
            }

            $config->getName()->willReturn('name');
            $config->getOptions()->willReturn(['option' => 'value']);
            $config->getMetadata()->willReturn(new Metadata(['task' => 'taskName']));
            $isCalled = true;

            return $config->getWrappedObject();
        });
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LazyTaskConfig::class);
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

    public function it_contains_meta(): void
    {
        $this->getMetadata()->toArray()['task']->shouldBe('taskName');
    }

    public function it_only_fetches_proxy_once(): void
    {
        $this->getName()->shouldBe('name');
        $this->getName()->shouldBe('name');
        $this->getOptions()->shouldBe(['option' => 'value']);
        $this->getMetadata()->toArray()['task']->shouldBe('taskName');
    }
}
