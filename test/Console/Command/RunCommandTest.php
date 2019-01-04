<?php

namespace GrumPHPTest\Console\Command;

use GrumPHP\Console\Command\RunCommand;
use PHPUnit\Framework\TestCase;

class RunCommandTest extends TestCase
{
    /**
     * @test
     * @param string $valueString
     * @param array $expected
     * @dataProvider parses_comma_separated_options_dataProvider
     */
    function parses_comma_separated_options(string $valueString, array $expected)
    {
        $command = new class extends RunCommand{
            public function __construct()
            {
            }

            public function parseCommaSeparatedOption($str)
            {
                return parent::parseCommaSeparatedOption(... func_get_args());
            }
        };

        $actual = $command->parseCommaSeparatedOption($valueString);
        $actual = array_values($actual);

        $this->assertEquals($expected, $actual);
    }


    public function parses_comma_separated_options_dataProvider()
    {
        return [
            "default" => [
                "valueString"  => "foo,bar",
                "expected"  => [
                    "foo",
                    "bar"
                ],
            ],
            "trims values" => [
                "valueString"  => "foo , bar",
                "expected"  => [
                    "foo",
                    "bar"
                ],
            ],
            "empty" => [
                "valueString"  => "",
                "expected"  => [],
            ],
            "empty after trim" => [
                "valueString"  => " ",
                "expected"  => [],
            ],
        ];
    }
}
