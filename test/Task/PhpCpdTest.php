<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpCpdParallel;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpCpdTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpCpdParallel::getStaticName());
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

//        $services = [
//            "services" => [
//                "task.phpcpd_parallel" => [
//                    "class"     => PhpCpdParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "phpcpd_parallel",
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
                                "phpcpd_parallel" => [
                                    'directory' => '.',
                                    'exclude' => ['vendor'],
                                    'names_exclude' => [],
                                    'regexps_exclude' => [],
                                    'fuzzy' => false,
                                    'min_lines' => 5,
                                    'min_tokens' => 70,
                                    'triggered_by' => ['php'],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpcpd' '--exclude=vendor' '--min-lines=5' '--min-tokens=70' '--names=*.php' '.'",
            ],
        ];
    }
}
