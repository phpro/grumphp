<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ForbiddenStaticMethodCallsVisitor;

class ForbiddenStaticMethodCallsVisitorTest extends AbstractVisitorTest
{
    /**
     * @return ForbiddenStaticMethodCallsVisitor
     */
    protected function getVisitor(): ContextAwareVisitorInterface
    {
        $visitor = new ForbiddenStaticMethodCallsVisitor();
        $visitor->configure(array(
           'blacklist' => array('Dumper\StaticDumper::dump', 'My\Dumper::dump', 'Dumper\Alias::dump'),
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
    function it_does_not_allow_blacklisted_static_method_calls()
    {
        $code = <<<EOC
<?php
use Dumper\StaticDumper;
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
ValidStatic::method();
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
