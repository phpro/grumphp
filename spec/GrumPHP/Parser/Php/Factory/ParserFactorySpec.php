<?php

namespace spec\GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\Factory\ParserFactory;
use GrumPHP\Task\PhpParser;
use PhpParser\Parser;
use PhpSpec\ObjectBehavior;

class ParserFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParserFactory::class);
    }

    function it_can_create_a_parser_from_task_options()
    {
        $options = ['kind' => PhpParser::KIND_PHP7];
        $this->createFromOptions($options)->shouldBeAnInstanceOf(Parser::class);
    }
}
