<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\ComposerParallel;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ComposerTest extends TestCase
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
        $task    = $this->resolveTask($app, ComposerParallel::getStaticName());
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
//                "task.composer_parallel" => [
//                    "class"     => ComposerParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                        "@filesystem",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "composer_parallel",
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
                                "composer_parallel" => [
                                    'file'                => './composer.json',
                                    'no_check_all'        => false,
                                    'no_check_lock'       => false,
                                    'no_check_publish'    => false,
                                    'no_local_repository' => false,
                                    'with_dependencies'   => false,
                                    'strict'              => false,
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'composer' 'validate' './composer.json'",
            ],
        ];
    }
}
