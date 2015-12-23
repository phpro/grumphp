<?php

namespace spec\GrumPHP\Task;

use PhpSpec\ObjectBehavior;

/**
 * Class AbstractLinterTaskSpec
 *
 * @package spec\GrumPHP\Task
 */
abstract class AbstractLinterTaskSpec extends ObjectBehavior
{

    function it_is_a_task()
    {
        $this->shouldImplement('GrumPHP\Task\TaskInterface');
    }

    function it_should_handle_ignore_patterns()
    {
        $options = $this->getConfigurableOptions();
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
    }
}
