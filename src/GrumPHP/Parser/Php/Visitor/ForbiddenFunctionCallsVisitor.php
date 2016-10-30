<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ForbiddenFunctionCallsVisitor
 *
 * @package GrumPHP\Parser\Php\Visitor
 */
class ForbiddenFunctionCallsVisitor extends AbstractVisitor implements ConfigurableVisitorInterface
{
    /**
     * @var array
     */
    private $functions = array();

    /**
     * @var array
     */
    private $classMethods = array();

    /**
     * @var array
     */
    private $staticMethods = array();

    /**
     * @param array $options
     */
    public function configure(array $options)
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults(array(
            'functions' => array(),
            'class_methods' => array(),
            'static_methods' => array()
        ));

        $resolver->setAllowedTypes('functions', array('array'));
        $resolver->setAllowedTypes('class_methods', array('array'));
        $resolver->setAllowedTypes('static_methods', array('array'));

        $config = $resolver->resolve($options);
        $this->functions = $config['functions'];
        $this->classMethods = $config['class_methods'];
        $this->staticMethods = $config['static_methods'];
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $this->detectForbiddenFunctionCall($node);
        $this->detectForbiddenClassMethodCall($node);
        $this->detectForbiddenStaticMethodcalls($node);
    }

    /**
     * @param Node $node
     */
    private function detectForbiddenFunctionCall(Node $node)
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return;
        }

        $function = $node->name;
        if (!in_array($function, $this->functions)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" function call', $function),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }

    /**
     * @param Node $node
     */
    private function detectForbiddenClassMethodCall(Node $node)
    {
        if (!$node instanceof Node\Expr\MethodCall || !isset($node->var->name)) {
            return;
        }

        $variable = $node->var->name;
        $method = $node->name;
        $normalized = sprintf('$%s->%s', $variable, $method);
        if (!in_array($normalized, $this->classMethods)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" method call', $normalized),
            $node->getline(),
            ParseError::TYPE_ERROR
        );
    }

    /**
     * @param Node $node
     */
    private function detectForbiddenStaticMethodcalls(Node $node)
    {
        if (!$node instanceof Node\Expr\StaticCall) {
            return;
        }

        $class = implode('\\', $node->class->parts);
        $method = $node->name;
        $normalized = sprintf('%s::%s', $class, $method);

        if (!in_array($normalized, $this->staticMethods)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" static method call', $normalized),
            $node->getline(),
            ParseError::TYPE_ERROR
        );
    }
}
