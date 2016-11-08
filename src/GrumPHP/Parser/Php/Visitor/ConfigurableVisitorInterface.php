<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\NodeVisitor;

/**
 * Interface ConfigurableVisitorInterface
 *
 * @package GrumPHP\Parser\Php\Visitor
 */
interface ConfigurableVisitorInterface extends NodeVisitor
{
    /**
     * @param array $options
     */
    public function configure(array $options);
}
