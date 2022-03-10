<?php

namespace spec\GrumPHP\Task\Config;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Task\Config\Metadata;

class MetadataSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Metadata::class);
    }

    public function it_contains_an_options_resolver_with_default(): void
    {
        $resolver = $this->getConfigurableOptions();
        $result = $resolver->resolve([]);
        $result->shouldBe([
            'priority' => 0,
            'blocking' => true,
            'enabled' => true,
            'task' => '',
            'label' => '',
        ]);
    }

    public function it_contains_default_options(): void
    {
        $this->beConstructedWith([]);
        $this->priority()->shouldBe(0);
        $this->isEnabled()->shouldBe(true);
        $this->isBlocking()->shouldBe(true);
        $this->task()->shouldBe('');
        $this->label()->shouldBe('');
    }

    public function it_contains_a_priority(): void
    {
        $this->beConstructedWith(['priority' => $priority = 10]);
        $this->priority()->shouldBe($priority);
    }

    public function it_knows_if_blocking(): void
    {
        $this->beConstructedWith(['blocking' => true]);
        $this->isBlocking()->shouldBe(true);
    }

    public function it_knows_if_enabled(): void
    {
        $this->beConstructedWith(['enabled' => true]);
        $this->isEnabled()->shouldBe(true);
    }

    public function it_knows_if_not_blocking(): void
    {
        $this->beConstructedWith(['blocking' => false]);
        $this->isBlocking()->shouldBe(false);
    }

    public function it_knows_task(): void
    {
        $this->beConstructedWith(['task' => $taskName = 'taskName']);
        $this->task()->shouldBe($taskName);
    }

    public function it_knows_label(): void
    {
        $this->beConstructedWith(['label' => $label = 'label']);
        $this->label()->shouldBe($label);
    }

    public function it_can_convert_to_array(): void
    {
        $this->beConstructedWith([]);
        $this->toArray()->shouldBe(
            $this->getConfigurableOptions()->resolve([])
        );
    }
}
