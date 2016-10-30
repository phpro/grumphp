<?php

namespace spec\GrumPHP\Parser\Php\Configurator;

use GrumPHP\Parser\Php\Configurator\TraverserConfigurator;
use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TraverserConfiguratorSpec
 *
 * @package spec\GrumPHP\Parser\Php\Configurator
 * @mixin TraverserConfigurator
 */
class TraverserConfiguratorSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Parser\Php\Configurator\TraverserConfigurator');
    }
    
    
    function it_throw_an_exception_if_a_context_is_not_set(NodeTraverserInterface $traverser)
    {
        $this->registerOptions(array('visitors' => array()));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringConfigure($traverser);
    }

    function it_throw_an_exception_if_no_visitors_are_configured(ParserContext $context, NodeTraverserInterface $traverser)
    {
        $this->registerContext($context);
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringConfigure($traverser);
    }
    
    function it_loads_standard_enabled_visitors(
        ContainerInterface $container,
        ParserContext $context,
        NodeTraverserInterface $traverser,
        NodeVisitor $visitor
    ) {
        $visitorAlias = 'standard_enabled_visitor';
        $this->registerVisitorId($visitorAlias, $visitorAlias);
        $this->registerStandardEnabledVisitor($visitorAlias, null);
        $this->registerContext($context);
        $this->registerOptions(array('visitors' => array()));
        $container->get($visitorAlias)->willReturn($visitor);

        $traverser->addVisitor($visitor)->shouldBeCalled();

        $this->configure($traverser);
    }
    
    function it_loads_configured_visitors_from_task_configuration()
    {
        
    }
    
    function it_does_not_load_unknown_visitors()
    {
        
    }
    
    function it_does_not_load_unused_visitors()
    {
        
    }

    function it_should_append_the_context_to_a_context_aware_visitor()
    {

    }

    function it_should_pass_visitor_configuration_to_a_configuration_aware_visitor()
    {

    }
}
