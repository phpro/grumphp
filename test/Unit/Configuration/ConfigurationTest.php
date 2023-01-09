<?php
declare(strict_types=1);

namespace GrumPHPTest\Unit\Configuration;

use GrumPHP\Configuration\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideConfigs
     */
    public function it_can_be_configuered(array $config, array $expected): void
    {
        $actual = $this->parse($config);

        self::assertArrayContains($expected, $actual);
    }

    public function provideConfigs()
    {
        yield 'ascii_not_set' => [
            [],
            ['ascii' => [
                'failed' => 'grumphp-grumpy.txt',
                'succeeded' => 'grumphp-happy.txt',
            ]]
        ];
        yield 'ascii_nulled_out_items' => [
            ['ascii' => [
                'failed' => null,
                'succeeded' => null,
            ]],
            ['ascii' => [
                'failed' => null,
                'succeeded' => null,
            ]]
        ];
        yield 'ascii_custom' => [
            ['ascii' => [
                'failed' => 'custom-failure.txt',
                'succeeded' => 'custom-happy.txt',
            ]],
            ['ascii' => [
                'failed' => 'custom-failure.txt',
                'succeeded' => 'custom-happy.txt',
            ]]
        ];
        yield 'ascii_nulled_out' => [
            ['ascii' => null],
            ['ascii' => [
                'failed' => null,
                'succeeded' => null,
            ]]
        ];
    }

    private function parse(array $config): array
    {
        $configuration = new Configuration();
        $processor = new Processor();

        return $processor->process($configuration->getConfigTreeBuilder()->buildTree(), ['grumphp' => $config]);
    }

    private function assertArrayContains(array $needle, array $haystack)
    {
        self::assertSame(
            array_intersect_key($haystack, $needle),
            $needle
        );
    }
}
