<?php

namespace GrumPHPTest\Console\Command;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Command\RunCommand;
use GrumPHP\Locator\RegisteredFiles;
use PHPUnit\Framework\TestCase;

class RunCommandTest extends TestCase
{
    /**
     * @test
     * @param null $valueString
     * @param null $expected
     * @dataProvider parses_comma_separated_options_dataProvider
     * @throws \ReflectionException
     */
    function parses_comma_separated_options($valueString = null, $expected = null)
    {
        /**
         * @var GrumPHP $grumPhp
         */
        $grumPhp = $this->createMock(GrumPHP::class);
        /**
         * @var RegisteredFiles $registeredFiles
         */
        $registeredFiles = $this->createMock(RegisteredFiles::class);

        $command = new RunCommand($grumPhp, $registeredFiles);
        $method  = new \ReflectionMethod($command, "parseCommaSeparatedOption");
        $method->setAccessible(true);

        $actual = $method->invoke($command, $valueString);

        $this->assertEquals($expected, $actual);
    }


    public function parses_comma_separated_options_dataProvider()
    {
        return [
            "default" => [
                "valueString"  => "foo,bar",
                "expected"  => [
                    "foo" => "foo",
                    "bar" => "bar"
                ],
            ],
            "trims values" => [
                "valueString"  => "foo , bar",
                "expected"  => [
                    "foo" => "foo",
                    "bar" => "bar"
                ],
            ],
            "deduplicates values" => [
                "valueString"  => "foo,bar,bar",
                "expected"  => [
                    "foo" => "foo",
                    "bar" => "bar"
                ],
            ],
            "null" => [
                "valueString"  => null,
                "expected"  => [],
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
