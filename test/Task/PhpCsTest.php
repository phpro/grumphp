<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpCsParallel;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpCsTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpCsParallel::getStaticName());
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

        return [
            "default" => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "phpcs_parallel" => [
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
                "expected" => "'phpcs' '--standard=standard' '--report=full' '--ignore=ignore_patterns' '--parallel=1' '-s' '--report-json'",
            ],
        ];
    }

    /**
     * @dataProvider buildProcessWithInstalledDependency_dataProvider
     * @param array $config
     * @param string $expected
     * @throws \ReflectionException
     */
    public function test_buildProcessWithInstalledDependency(array $config, string $expected)
    {
        $app = $this->resolveApplication($config);
        /**
         * @var PhpCsParallel $task
         */
        $task    = $this->resolveTask($app, PhpCsParallel::getStaticName(), false);
        $context = $this->resolveContext();

        $process = $task->resolveProcess($context);

        // Note:
        // we can use the sprintf variant instead of a fixed value
        // if we want to actually "resolve" the executable
        // which is helpful for end2end tests.
        $pathToExecutable = $task->getExecutablePath();
        $expected = sprintf($expected, $pathToExecutable);

        $this->assertProcessCommand($expected,$process);
    }

    /**
     * @return array
     */
    public function buildProcessWithInstalledDependency_dataProvider()
    {
        $services = [];

        return [
            "default" => [
                "data"     => [
                        "parameters" => [
                            "tasks" => [
                                "phpcs_parallel" => [
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
                "expected" => "'%s' '--standard=standard' '--report=full' '--ignore=ignore_patterns' '--parallel=1' '-s' '--report-json'",
            ],
        ];
    }
}
