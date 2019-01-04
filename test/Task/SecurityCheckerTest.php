<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\SecuritycheckerParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class SecurityCheckerTest extends TestCase
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
        $task    = $this->resolveTask($app, SecurityCheckerParallel::getStaticName());
        $context = $this->resolveContext();

        /**
         * @var Process $process
         */
        $process = $task->resolveProcess($context);

        $actual = $process->getCommandLine();

        $this->assertEquals($expected, $actual);
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
//                "task.securitychecker_parallel" => [
//                    "class"     => SecurityCheckerParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "securitychecker_parallel",
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
                                "securitychecker_parallel" => [
                                    'lockfile' => './composer.lock',
                                    'format' => null,
                                    'end_point' => null,
                                    'timeout' => null,
                                    'run_always' => false,
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'security-checker' 'security:check' './composer.lock'",
            ],
        ];
    }
}
