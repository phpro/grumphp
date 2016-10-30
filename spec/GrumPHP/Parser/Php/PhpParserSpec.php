<?php

namespace spec\GrumPHP\Parser\Php;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Factory\ParserFactory;
use GrumPHP\Parser\Php\Factory\TraverserFactory;
use GrumPHP\Parser\Php\PhpParser;
use PhpParser\Error;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class PhpParserSpec
 *
 * @package spec\GrumPHP\Parser\Php
 * @mixin PhpParser
 */
class PhpParserSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    protected $tempFile;

    function let(
        ParserFactory $parserFactory,
        TraverserFactory $traverserFactory,
        Parser $parser,
        NodeTraverserInterface $traverser
    ) {
        $this->beConstructedWith($parserFactory, $traverserFactory);
        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpparser');
        $parserFactory->createFromOptions(Argument::any())->willReturn($parser);
        $traverserFactory->createForTaskContext(Argument::cetera())->willReturn($traverser);
        $parser->parse(Argument::any())->willReturn([]);
    }

    function letgo()
    {
        unlink($this->tempFile);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Parser\Php\PhpParser');
    }

    function it_uses_parser_options(
        ParserFactory $parserFactory,
        TraverserFactory $traverserFactory,
        Parser $parser,
        NodeTraverserInterface $traverser
    ) {
        $file = new \SplFileInfo($this->tempFile);
        $this->setParserOptions($options = ['kind' => 'php7']);

        $parserFactory->createFromOptions($options)->shouldBeCalled()->willReturn($parser);
        $traverserFactory->createForTaskContext($options, Argument::that(function (ParserContext $context) use ($file) {
            return $context->getFile() === $file
                && $context->getErrors() instanceof ParseErrorsCollection;
        }))->shouldBeCalled()->willReturn($traverser);

        $this->parse($file);
    }

    function it_parses_a_file(NodeTraverserInterface $traverser)
    {
        $file = new \SplFileInfo($this->tempFile);
        $traverser->traverse(array())->shouldBeCalled();
        $errors = $this->parse($file);

        $errors->shouldBeAnInstanceOf('GrumPHP\Collection\ParseErrorsCollection');
        $errors->count()->shouldBe(0);
    }

    function it_catches_parse_exceptions(Parser $parser)
    {
        $file = new \SplFileInfo($this->tempFile);
        $parser->parse(Argument::any())->willThrow(new Error('Error ....'));
        $errors = $this->parse($file);

        $errors->shouldBeAnInstanceOf('GrumPHP\Collection\ParseErrorsCollection');
        $errors->count()->shouldBe(1);
    }
}
