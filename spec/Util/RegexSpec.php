<?php

namespace spec\GrumPHP\Util;

use PhpSpec\ObjectBehavior;

class RegexSpec extends ObjectBehavior
{
    function it_will_handle_regex_input()
    {
        $this->beConstructedWith('#test#');
        $this->__toString()->shouldBe('#test#');
    }

    function it_will_handle_glob_input()
    {
        $this->beConstructedWith('test');
        $this->__toString()->shouldBe('#^(?=[^\.])test$#');
    }

    function it_should_be_able_to_add_pattern_modifier_when_no_modifiers_are_available()
    {
        $this->beConstructedWith('#test#');
        $this->addPatternModifier('m');

        $this->__toString()->shouldBe('#test#m');
    }

    function it_should_be_able_to_add_pattern_modifier_when__modifiers_are_available()
    {
        $this->beConstructedWith('#test#i');
        $this->addPatternModifier('m');

        $this->__toString()->shouldBe('#test#im');
    }

    function it_should_not_add_pattern_modifier_twice()
    {
        $this->beConstructedWith('#test#i');
        $this->addPatternModifier('i');

        $this->__toString()->shouldBe('#test#i');
    }

    function it_should_know_which_pattern_modifiers_can_be_user()
    {
        $this->beConstructedWith('#test#i');
        $this->shouldThrow('RuntimeException')->duringAddPatternModifier('invalid');
        $this->shouldThrow('RuntimeException')->duringAddPatternModifier('a');
    }
}
