<?php

namespace GrumPHPTest\Task;

use GrumPHP\Util\Str;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    use GrumPHPTestHelperTrait;

    /**
     * @test
     * @param string $valueString
     * @param array $expected
     * @dataProvider explodeWithCleanup_dataProvider
     */
    function explodeWithCleanup(string $delimiter, string $valueString, array $expected)
    {
        $actual = Str::explodeWithCleanup($delimiter, $valueString);
        $actual = array_values($actual);

        $this->assertEquals($expected, $actual);
    }

    public function explodeWithCleanup_dataProvider()
    {
        return [
            "default"          => [
                "delimiter"   => ",",
                "valueString" => "foo,bar",
                "expected"    => [
                    "foo",
                    "bar",
                ],
            ],
            "other delimiter"          => [
                "delimiter"   => ";",
                "valueString" => "foo;bar",
                "expected"    => [
                    "foo",
                    "bar",
                ],
            ],
            "trims values"     => [
                "delimiter"   => ",",
                "valueString" => "foo , bar",
                "expected"    => [
                    "foo",
                    "bar",
                ],
            ],
            "empty"            => [
                "delimiter"   => ",",
                "valueString" => "",
                "expected"    => [],
            ],
            "empty after trim" => [
                "delimiter"   => ",",
                "valueString" => " ",
                "expected"    => [],
            ],
        ];
    }
}
