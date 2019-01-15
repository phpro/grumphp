<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpCbf;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpCbfTest extends TestCase
{
    use GrumPHPTestHelperTrait;

    /**
     * @dataProvider buildProcess_dataProvider
     * @param array $config
     * @param string $expected
     * @throws \ReflectionException
     */
    public function test_buildProcess(array $config, string $expected)
    {
        $app = $this->resolveApplication($config);
        /**
         * @var Paratest $task
         */
        $task    = $this->resolveTask($app, PhpCbf::getStaticName());
        $context = $this->resolveContext();

        /**
         * @var Process $process
         */
        $process = $task->resolveProcess($context);

        $this->assertProcessCommand($expected, $process);
    }

    /**
     * @return array
     */
    public function buildProcess_dataProvider()
    {
        $services = [];

        return [
            "default"       => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                PhpCbf::getStaticName() => [
                                    "parallel"           => 1,
                                    "standard"           => 'standard',
                                    "show_sniffs"        => true,
                                    "ignore_patterns"    => ['ignore_patterns'],
                                    "whitelist_patterns" => ['whitelist_patterns'],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpcbf' '--standard=standard' '--report=full' '--ignore=ignore_patterns' '--parallel=1' '-s' '--report-json'",
            ],
        ];
    }
}
