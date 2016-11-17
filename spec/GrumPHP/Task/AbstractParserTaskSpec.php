<?php

namespace spec\GrumPHP\Task;

use PhpSpec\ObjectBehavior;

/**
 * Class AbstractParserTaskSpec
 *
 * @package spec\GrumPHP\Task
 */
abstract class AbstractParserTaskSpec extends ObjectBehavior
{

    function it_is_a_task()
    {
        $this->shouldImplement('GrumPHP\Task\TaskInterface');
    }

    function it_should_handle_ignore_patterns()
    {
        $options = $this->getConfigurableOptions();
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
        $options->getDefinedOptions()->shouldContain('triggered_by');
    }
}
