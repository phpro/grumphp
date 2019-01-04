<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpMndParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpMndTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpMndParallel::getStaticName());
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

//        $services = [
//            "services" => [
//                "task.phpmnd_parallel" => [
//                    "class"     => PhpMndParallel::class,
//                    "arguments" => [
//                        "@config",
//                        "@process_builder",
//                        "@formatter.raw_process",
//                    ],
//                    "tags"      => [
//                        [
//                            "name"   => "grumphp.task",
//                            "config" => "phpmnd_parallel",
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
                                "phpmnd_parallel" => [
                                    "directory"          => "directory",
                                    "whitelist_patterns" => ['whitelist_patterns'],
                                    "exclude"            => ['exclude'],
                                    "exclude_name"       => ['exclude_name'],
                                    "exclude_path"       => ['exclude_path'],
                                    "extensions"         => ['extensions'],
                                    "ignore_numbers"     => ['ignore_numbers'],
                                    "ignore_funcs"       => ['ignore_funcs'],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpmnd' '--exclude=exclude' '--exclude-file=exclude_name' '--exclude-path=exclude_path' '--extensions=extensions' '--ignore-numbers=ignore_numbers' '--suffixes=php' '--ignore-funcs=ignore_funcs' '--non-zero-exit-on-violation' 'directory'",
            ],
        ];
    }
}
