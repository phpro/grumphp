<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ForbiddenFunctionCallsVisitor;

class ForbiddenFunctionCallsVisitorTest extends AbstractVisitorTest
{
    protected function getVisitor(): ContextAwareVisitorInterface
    {
        $visitor = new ForbiddenFunctionCallsVisitor();
        $visitor->configure([
           'blacklist' => ['var_dump'],
        ]);

        return $visitor;
    }

    /**
     * @test
     */
    function it_is_a_configurable_visitor()
    {
        $this->assertInstanceOf(ConfigurableVisitorInterface::class, $this->getVisitor());
    }

    /**
     * @test
     */
    function it_does_not_allow_blacklisted_functions()
    {
        $code = <<<EOC
<?php
var_dump('test');
EOC;

        $errors = $this->visit($code);

        $this->assertCount(1, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(2, $errors[0]->getLine());
    }

    /**
     * @test
     */
    function it_allows_code_that_does_not_use_invalid_functions()
    {
        $code = <<<EOC
<?php
validMethod();
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
