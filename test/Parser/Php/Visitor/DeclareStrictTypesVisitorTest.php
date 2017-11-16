<?php

namespace GrumPHPTest\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Visitor\DeclareStrictTypesVisitor;

class DeclareStrictTypesVisitorTest extends AbstractVisitorTest
{
    /**
     * @return DeclareStrictTypesVisitor
     */
    protected function getVisitor()
    {
        return new DeclareStrictTypesVisitor();
    }

    /**
     * @test
     */
    public function it_enforces_strict_types()
    {
        $code = <<<EOC
<?php

class SomeClass
{
}
EOC;

        $errors = $this->visit($code);
        $this->assertCount(1, $errors);
        $this->assertEquals(ParseError::TYPE_ERROR, $errors[0]->getType());
        $this->assertEquals(-1, $errors[0]->getLine());
    }

    /**
     * @test
     */
    public function it_doesnt_allow_strict_types_with_value_0()
    {
        $code = <<<EOC
<?php
declare(strict_types = 0);

class SomeClass
{
}
EOC;

        $errors = $this->visit($code);
        $this->assertCount(1, $errors);
    }

    /**
     * @test
     */
    public function it_allows_code_with_strict_types_set()
    {
        $code = <<<EOC
<?php
declare(strict_types = 1);

class SomeClass
{
}
EOC;

        $errors = $this->visit($code);
        $this->assertCount(0, $errors);
    }
}
