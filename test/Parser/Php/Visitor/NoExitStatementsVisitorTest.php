<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use GrumPHP\Parser\Php\Visitor\NoExitStatementsVisitor;

class NoExitStatementsVisitorTest extends AbstractVisitorTest
{
    /**
     * @return NoExitStatementsVisitor
     */
    protected function getVisitor(): ContextAwareVisitorInterface
    {
        return new NoExitStatementsVisitor();
    }

    /**
     * @test
     */
    function it_does_not_allow_exit_statements()
    {
        $code = <<<EOC
<?php
exit;
die('ok');
EOC;

        $errors = $this->visit($code);
        $this->assertCount(2, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(2, $errors[0]->getLine());
        $this->assertEquals(3, $errors[1]->getLine());
    }

    /**
     * @test
     */
    function it_allows_code_with_no_exit_statements()
    {
        $code = <<<EOC
<?php
// Some valid code here...
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
