<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\NodeVisitor;

interface ConfigurableVisitorInterface extends NodeVisitor
{
    /**
     * @param array $options
     */
    public function configure(array $options);
}
