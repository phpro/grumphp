<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\ComposerRequireCheckerParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ComposerRequireCheckerTest extends TestCase
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
        $task    = $this->resolveTask($app, ComposerRequireCheckerParallel::getStaticName());
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
//                "task.composer_require_checker_parallel" => [
//                    "class"     => ComposerRequireCheckerParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "composer_require_checker_parallel",
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
                                "composer_require_checker_parallel" => [
                                    'composer_file'       => 'composer.json',
                                    'config_file'         => null,
                                    'ignore_parse_errors' => false,
                                    'triggered_by'        => ['composer.json', 'composer.lock', '*.php'],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'composer-require-checker' 'check' '--no-interaction' 'composer.json'",
            ],
        ];
    }
}
