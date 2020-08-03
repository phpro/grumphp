<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForbiddenClassMethodCallsVisitor extends AbstractVisitor implements ConfigurableVisitorInterface
{
    /**
     * @var array
     */
    private $blacklist = [];

    public function configure(array $options): void
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
     * @psalm-suppress UndefinedPropertyFetch
     * @psalm-suppress PossiblyInvalidCast
     * @psalm-suppress ImplicitToStringCast
     * @psalm-suppress InvalidArgument
     */
    public function leaveNode(Node $node): void
    {
        if (!$node instanceof Node\Expr\MethodCall || !isset($node->var->name)) {
            return;
        }

        $variable = $node->var->name;
        $method = $node->name;
        $normalized = sprintf('$%s->%s', $variable, $method);
        if (!\in_array($normalized, $this->blacklist, true)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" method call', $normalized),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }
}
