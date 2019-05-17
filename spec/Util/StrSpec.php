<?php

namespace spec\GrumPHP\Util;

use PhpSpec\ObjectBehavior;

class StrSpec extends ObjectBehavior
{
    function it_should_find_a_part_of_a_string_by_one_of_the_provided_needles()
    {
        $this::containsOneOf('a;randomText-written by me', ['a', 'me'])->shouldBe(true);
        $this::containsOneOf('a;randomText-written by me', ['Text'])->shouldBe(true);

        $this::containsOneOf('a;randomText-written by me', ['this does not exist'])->shouldBe(false);
        $this::containsOneOf('a;randomText-written by me', ['text'])->shouldBe(false);
    }

    function it_should_split_a_string_by_a_delimiter_and_result_in_a_unique_list()
    {
        $this::explodeWithCleanup(',', ' a random,list, of things ')->shouldBe([
            'a random', 'list', 'of things'
        ]);
        $this::explodeWithCleanup(',', 'double,double')->shouldBe([
            'double',
        ]);
        $this::explodeWithCleanup(',', '')->shouldBe([]);
    }
}
