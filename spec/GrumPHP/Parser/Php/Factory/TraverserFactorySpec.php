<?php

namespace spec\GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\Configurator\TraverserConfigurator;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Factory\TraverserFactory;
use PhpParser\NodeTraverser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TraverserFactorySpec extends ObjectBehavior
{
    function let(TraverserConfigurator $configurator)
    {
        $this->beConstructedWith($configurator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TraverserFactory::class);
    }

    function it_can_create_a_task_and_context_specific_traverser(TraverserConfigurator $configurator, ParserContext $context)
    {
        $taskOptions = ['visitors' => []];

        $configurator->registerOptions($taskOptions)->shouldBeCalled();
        $configurator->registerContext($context)->shouldBeCalled();
        $configurator->configure(Argument::type(NodeTraverser::class))->shouldBeCalled();

        $this->createForTaskContext($taskOptions, $context)->shouldBeAnInstanceOf(NodeTraverser::class);
    }
}
