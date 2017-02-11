<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForbiddenStaticMethodCallsVisitor extends AbstractVisitor implements ConfigurableVisitorInterface
{
    /**
     * @var array
     */
    private $blacklist = [];

    /**
     * @param array $options
     */
    public function configure(array $options)
    {
        $resolver = new OptionsResolver();

        $resolver->setDefaults([
            'blacklist' => [],
        ]);

        $resolver->setAllowedTypes('blacklist', ['array']);

        $config = $resolver->resolve($options);
        $this->blacklist = $config['blacklist'];
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Expr\StaticCall) {
            return;
        }

        $class = implode('\\', $node->class->parts);
        $method = $node->name;
        $normalized = sprintf('%s::%s', $class, $method);

        if (!in_array($normalized, $this->blacklist)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" static method call', $normalized),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }
}
