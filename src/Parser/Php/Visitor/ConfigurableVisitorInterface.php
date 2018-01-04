<?php declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\NodeVisitor;

interface ConfigurableVisitorInterface extends NodeVisitor
{
    /**
     * @param array $options
     */
    public function configure(array $options);
}
