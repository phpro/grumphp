<?php declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForbiddenFunctionCallsVisitor extends AbstractVisitor implements ConfigurableVisitorInterface
{
    /**
     * @var array
     */
    private $blacklist = [];

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

    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return;
        }

        $function = $node->name;
        if (!in_array($function, $this->blacklist)) {
            return;
        }

        $this->addError(
            sprintf('Found blacklisted "%s" function call', $function),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }
}
