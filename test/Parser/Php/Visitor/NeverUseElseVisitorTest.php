<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use GrumPHP\Parser\Php\Visitor\NeverUseElseVisitor;

class NeverUseElseVisitorTest extends AbstractVisitorTest
{
    /**
     * @return NeverUseElseVisitor
     */
    protected function getVisitor(): ContextAwareVisitorInterface
    {
        return new NeverUseElseVisitor();
    }

    /**
     * @test
     */
    function it_does_not_allow_else_statements()
    {
        $code = <<<EOC
<?php
if (\$something) {
    return true;
} elseif (\$something2) {
    return true;
} else {
    return true;
}
EOC;

        $errors = $this->visit($code);
        $this->assertCount(2, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(4, $errors[0]->getLine());
        $this->assertEquals(6, $errors[1]->getLine());
    }

    /**
     * @test
     */
    function it_allows_code_with_no_else_statements()
    {
        $code = <<<EOC
<?php
if (true) {
    return;
}
if (false) {
    return;
}
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
