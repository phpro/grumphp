<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ForbiddenClassMethodCallsVisitor;

class ForbiddenClassMethodCallsVisitorTest extends AbstractVisitorTest
{
    protected function getVisitor(): ContextAwareVisitorInterface
    {
        $visitor = new ForbiddenClassMethodCallsVisitor();
        $visitor->configure(array(
           'blacklist' => array('$dumper->dump'),
        ));

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
    function it_does_not_allow_blacklisted_class_method_calls()
    {
        $code = <<<EOC
<?php
\$dumper = new ClassDumper();
\$dumper->dump('something');
\$this->dumper->dump('something');
EOC;

        $errors = $this->visit($code);
        $this->assertCount(2, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(3, $errors[0]->getLine());
        $this->assertEquals(4, $errors[1]->getLine());
    }

    /**
     * @test
     */
    function it_allows_code_that_does_not_use_invalid_functions()
    {
        $code = <<<EOC
<?php
\$some->validMethod();
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
