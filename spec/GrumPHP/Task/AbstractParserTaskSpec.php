<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;

abstract class AbstractParserTaskSpec extends ObjectBehavior
{
    function it_is_a_task()
    {
        $this->shouldImplement(TaskInterface::class);
    }

    function it_should_handle_ignore_patterns()
    {
        $options = $this->getConfigurableOptions();
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
        $options->getDefinedOptions()->shouldContain('triggered_by');
    }
}
