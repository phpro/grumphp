<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpMdParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpMdTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpMdParallel::getStaticName());
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
//                "task.phpmd_parallel" => [
//                    "class"     => PhpMdParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "phpmd_parallel",
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
                                "phpmd_parallel" => [
                                    "whitelist_patterns" => ['whitelist_patterns'],
                                    "exclude"            => ['exclude'],
                                    "ruleset"            => ["ruleset"],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpmd' 'text' 'ruleset' '--exclude' 'exclude'",
            ],
        ];
    }
}
