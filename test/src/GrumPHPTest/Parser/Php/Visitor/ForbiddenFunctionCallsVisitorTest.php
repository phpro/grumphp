<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ForbiddenFunctionCallsVisitor;

/**
 * Class ForbiddenFunctionCallsVisitorTest
 *
 * @package GrumPHPTest\Parser\Php\Visitor
 */
class ForbiddenFunctionCallsVisitorTest extends AbstractVisitorTest
{
    /**
     * @return ForbiddenFunctionCallsVisitor
     */
    protected function getVisitor()
    {
        $visitor = new ForbiddenFunctionCallsVisitor();
        $visitor->configure(array(
           'functions' => array('var_dump'),
           'class_methods' => array('$dumper->dump'),
           'static_methods' => array('Dumper\StaticDumper::dump', 'My\Dumper::dump', 'Dumper\Alias::dump'),
        ));

        return $visitor;
    }

    function it_is_a_configurable_visitor()
    {
        $this->assertInstanceOf('GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface', $this->getVisitor());
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
    function it_does_not_allow_blacklisted_static_method_calls()
    {
        $code = <<<EOC
<?php
use Dumper\StaticDumper As StaticDumper;
use Dumper\Alias As StaticDumperAlias;

StaticDumper::dump('something');
My\Dumper::dump('something');
StaticDumperAlias::dump('something');

\StaticDumper::dump('something');
EOC;

        $errors = $this->visit($code);
        $this->assertCount(3, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(5, $errors[0]->getLine());
        $this->assertEquals(6, $errors[1]->getLine());
        $this->assertEquals(7, $errors[2]->getLine());
    }

    /**
     * @test
     */
    function it_allows_code_that_does_not_use_invalid_functions()
    {
        $code = <<<EOC
<?php
validMethod();
\$some->validMethod();
ValidStatic::method();
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
