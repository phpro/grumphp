<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpStanParallel;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpStanTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpStanParallel::getStaticName());
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
//                "task.phpstan_parallel" => [
//                    "class"     => PhpStanParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "phpstan_parallel",
//                        ],
//                    ],
//                ],
//            ],
//        ];

        return [
            "default" => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "phpstan_parallel" => [
                                    "autoload_file"   => 'autoload_file',
                                    "configuration"   => 'configuration',
                                    "force_patterns"  => ["force_patterns"],
                                    "ignore_patterns" => ["ignore_patterns"],
                                    "level"           => 0,
                                    "triggered_by"    => ["triggered_by"],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpstan' 'analyse' '--autoload-file=autoload_file' '--configuration=configuration' '--level=0' '--no-ansi' '--no-interaction' '--no-progress'",
            ],
        ];
    }
}
