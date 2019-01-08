<?php

namespace GrumPHPTest\Task;

use GrumPHP\Task\Paratest;
use GrumPHP\Task\PhpLintParallel;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhpLintTest extends TestCase
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
        $task    = $this->resolveTask($app, PhpLintParallel::getStaticName());
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
                                "phplint_parallel" => [
                                    'jobs'            => 10,
                                    'exclude'         => ["exclude"],
                                    'ignore_patterns' => ["ignore_patterns"],
                                    'triggered_by'    => ['php', 'phtml', 'php3', 'php4', 'php5'],
                                ],
                            ],
                        ],
                    ]
                    +
                    $services,
                "expected" => "'parallel-lint' '--no-colors' '-j' '10' '--exclude' 'exclude'",
            ],
        ];
    }
}
