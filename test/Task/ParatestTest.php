<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ParatestTest extends TestCase
{
    use GrumPhpTestHelperTrait;

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
        $task    = $this->resolveTask($app, Paratest::getStaticName());
        $context = $this->resolveContext();

        /**
         * @var Process $process
         */
        $process = $task->resolveProcess($context);

        $this->assertProcessCommand($expected,$process);
    }

    /**
     * @return array
     */
    public function buildProcess_dataProvider()
    {
        $services = [];
//
//        $services = [
//            "services" => [
//                "task.paratest" => [
//                    "class"     => Paratest::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "paratest",
//                        ],
//                    ],
//                ],
//            ],
//        ];

        return [
            "default"     => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "paratest" => [
                                    'runner'        => 'runner',
                                    'coverage-xml'  => 'coverage-xml',
                                    'coverage-html' => 'coverage-html',
                                    'log-junit'     => 'log-junit',
                                    'testsuite'     => 'testsuite',
                                    'config'        => 'config',
                                    'processes'     => 1,
                                ],
                            ],
                        ],
                    ] + $services,
                "expected" => "'paratest' '--runner' 'runner' '--coverage-xml' 'coverage-xml' '--coverage-html' 'coverage-html' '--log-junit' 'log-junit' '--testsuite' 'testsuite' '-c' 'config' '-p' '1'",
            ],
            "with phpdbg" => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "paratest" => [
                                    'runner'   => 'runner',
                                    'debugger' => [
                                        'bin' => 'phpdbg',
                                        'args' => [
                                            '-qrr'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ] + $services,
                "expected" => "'phpdbg' '-qrr' 'paratest' '--runner' 'runner'",
            ],
        ];
    }
}
