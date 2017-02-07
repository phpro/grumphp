<?php

namespace spec\GrumPHP\Parser\Php\Configurator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\Php\Configurator\TraverserConfigurator;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TraverserConfiguratorSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TraverserConfigurator::class);
    }

    function it_throws_an_exception_if_a_context_is_not_set(NodeTraverserInterface $traverser)
    {
        $this->registerOptions(['visitors' => []]);
        $this->shouldThrow(RuntimeException::class)->duringConfigure($traverser);
    }

    function it_throws_an_exception_if_no_visitors_are_configured(ParserContext $context, NodeTraverserInterface $traverser)
    {
        $this->registerContext($context);
        $this->shouldThrow(RuntimeException::class)->duringConfigure($traverser);
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
        $this->registerOptions(['visitors' => []]);
        $container->get($visitorAlias)->willReturn($visitor);

        $traverser->addVisitor($visitor)->shouldBeCalled();

        $this->configure($traverser);
    }

    function it_loads_configured_visitors_from_task_configuration(
        ContainerInterface $container,
        ParserContext $context,
        NodeTraverserInterface $traverser,
        NodeVisitor $visitor
    ) {
        $visitorAlias = 'task_visitor';
        $this->registerVisitorId($visitorAlias, $visitorAlias);
        $this->registerContext($context);
        $this->registerOptions([
            'visitors' => [
            $visitorAlias => null
            ]
        ]);
        $container->get($visitorAlias)->willReturn($visitor);

        $traverser->addVisitor($visitor)->shouldBeCalled();

        $this->configure($traverser);
    }

    function it_throws_an_exception_if_the_configured_visitor_could_not_be_found(
        ParserContext $context,
        NodeTraverserInterface $traverser
    ) {
        $visitorAlias = 'unknown_visitor';
        $this->registerContext($context);
        $this->registerOptions([
            'visitors' => [
            $visitorAlias => null
            ]
        ]);

        $this->shouldThrow(RuntimeException::class)->duringConfigure($traverser);
    }

    function it_does_not_load_unused_visitors(
        ContainerInterface $container,
        ParserContext $context,
        NodeTraverserInterface $traverser,
        NodeVisitor $visitor
    ) {
        $visitorAlias = 'unused_visitor';
        $this->registerVisitorId($visitorAlias, $visitorAlias);
        $this->registerContext($context);
        $this->registerOptions(['visitors' => []]);
        $container->get($visitorAlias)->willReturn($visitor);

        $traverser->addVisitor($visitor)->shouldNotBeCalled();

        $this->configure($traverser);
    }

    function it_should_append_the_context_to_a_context_aware_visitor(
        ContainerInterface $container,
        ParserContext $context,
        NodeTraverserInterface $traverser,
        ContextAwareVisitorInterface $visitor
    ) {
        $visitorAlias = 'context_aware_visitor';
        $this->registerVisitorId($visitorAlias, $visitorAlias);
        $this->registerContext($context);
        $this->registerOptions([
            'visitors' => [
            $visitorAlias => null
            ]
        ]);
        $container->get($visitorAlias)->willReturn($visitor);

        $visitor->setContext($context)->shouldBeCalled();
        $traverser->addVisitor($visitor)->shouldBeCalled();

        $this->configure($traverser);
    }

    function it_should_pass_visitor_configuration_to_a_configuration_aware_visitor(
        ContainerInterface $container,
        ParserContext $context,
        NodeTraverserInterface $traverser,
        ConfigurableVisitorInterface $visitor
    ) {
        $visitorAlias = 'configurable_visitor';
        $configuration = ['key' => 'value'];
        $this->registerVisitorId($visitorAlias, $visitorAlias);
        $this->registerContext($context);
        $this->registerOptions([
            'visitors' => [
            $visitorAlias => $configuration
            ]
        ]);
        $container->get($visitorAlias)->willReturn($visitor);

        $visitor->configure($configuration)->shouldBeCalled();
        $traverser->addVisitor($visitor)->shouldBeCalled();

        $this->configure($traverser);
    }
}
