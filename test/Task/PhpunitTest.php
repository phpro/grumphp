<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpunitParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpunitTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpunitParallel::getStaticName());
        $context = $this->resolveContext();

        /**
         * @var Process $process
         */
        $process = $task->resolveProcess($context);

        $this->assertProcessCommand($expected,$process);;
    }

    /**
     * @return array
     */
    public function buildProcess_dataProvider()
    {
        $services = [];

        return [
            "default" => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "phpunit_parallel" => [
                                    'config_file' => "config_file",
                                    'testsuite' => "testsuite",
                                    'group' => [],
                                    'always_execute' => false,
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'phpunit' '--configuration=config_file' '--testsuite=testsuite'",
            ],
        ];
    }
}
