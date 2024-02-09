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
        $options = ['php_version' => null];
        $this->createFromOptions($options)->shouldBeAnInstanceOf(Parser::class);
    }

    function it_can_create_a_parser_from_empty_options()
    {
        $options = [];
        $this->createFromOptions($options)->shouldBeAnInstanceOf(Parser::class);
    }

    function it_can_create_a_parser_from_latest_php_version()
    {
        $options = ['php_version' => 'latest'];
        $this->createFromOptions($options)->shouldBeAnInstanceOf(Parser::class);
    }

    function it_can_create_a_parser_from_specific_latest_php_version()
    {
        $options = ['php_version' => '8.0'];
        $this->createFromOptions($options)->shouldBeAnInstanceOf(Parser::class);
    }

    function it_fails_on_invalid_version()
    {
        $options = ['php_version' => 'invalid'];
        $this->shouldThrow(\LogicException::class)->duringCreateFromOptions($options);
    }
}
