<?php

namespace %namespace%;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use %subject%;

/**
 * Class %name%
 */
class %name% extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(%subject_class%::class);
    }
}
