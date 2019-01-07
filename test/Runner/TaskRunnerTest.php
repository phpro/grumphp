<?php

namespace GrumPHPTest\Task;

use GrumPHP\Runner\ParallelOptions;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHPTest\Helper\ExternalTestTask;
use GrumPHPTest\Helper\GrumPhpTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class TaskRunnerTest extends TestCase
{
    use GrumPhpTestHelperTrait;

    /**
     * @dataProvider buildProcess_dataProvider
     * @param array $config
     * @param array $taskData
     * @param TaskRunnerContext|array|null $context
     * @param array $expected
     * @throws \ReflectionException
     */
    public function test_run(array $config = [], array $taskData = [], $context = null, array $expected = [])
    {
        $app = $this->resolveApplication($config);

        if ($expected["taskResults"] ?? null) {
            $actual = $this->runTasks($app, $taskData, $context);
            $this->assertTaskResults($expected["taskResults"], $actual);
        }

        if ($expected["output"] ?? null) {
            $actual = $this->runTasksWithOutput($app, $taskData, $context, OutputInterface::VERBOSITY_VERBOSE);
            $this->assertRunOutput($expected["output"], $actual);
        }

        // number of running processes > max
        // Process fails
        // Process succeeds
        // Process throws an exception
        // process "times out" ==> there is already a setting for that?
    }

    /**
     * @return array
     */
    public function buildProcess_dataProvider()
    {
        return [
            "non-parallel"                             => [
                "config"   => [
                    "parameters" => [
                        "ascii"                  => null,
                        "hide_circumvention_tip" => true,
                    ],
                ],
                "taskData" => [
                    [
                        new ExternalTestTask("foo", 0),
                    ],
                    [
                        new ExternalTestTask("fooBar", 1, "fooBar:out", "fooBar:err"),
                    ],
                    [
                        new ExternalTestTask("fooBaz", 1, "fooBaz:out", "fooBaz:err"),
                        [
                            "blocking" => false,
                        ],
                    ],
                ],
                "context"  => [
                    "parallelOptions" => null
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => 0,
                            "message"    => "",
                        ],
                        "fooBar" => [
                            "resultCode" => TaskResult::FAILED,
                            "message"    => "fooBar:out\n\nfooBar:err",
                        ],
                        "fooBaz" => [
                            "resultCode" => TaskResult::NONBLOCKING_FAILED,
                            "message"    => "fooBaz:out\n\nfooBaz:err",
                        ],
                    ],
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Running task 1/3: ExternalTestTask... ✔
Running task 2/3: ExternalTestTask... ✘
Running task 3/3: ExternalTestTask... ✘
fooBaz:out

fooBaz:err
fooBar:out

fooBar:err

RESULT
                    ,
                ],
            ],
            "parallel"                                 => [
                "config"   => [
                    "parameters" => [
                        "ascii"                  => null,
                        "hide_circumvention_tip" => true,
                    ],
                ],
                "taskData" => [
                    [
                        new ExternalTestTask("foo", 0, null, null, 0),
                    ],
                    [
                        new ExternalTestTask("fooBar", 1, "fooBar:out", "fooBar:err", 500),
                    ],
                    [
                        new ExternalTestTask("fooBaz", 1, "fooBaz:out", "fooBaz:err", 1000),
                        [
                            "blocking" => false,
                        ],
                    ],
                ],
                "context"  => [

                    "parallelOptions" => new ParallelOptions(0, 3),
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => 0,
                            "message"    => "",
                        ],
                        "fooBar" => [
                            "resultCode" => TaskResult::FAILED,
                            "message"    => "fooBar:out\n\nfooBar:err",
                        ],
                        "fooBaz" => [
                            "resultCode" => TaskResult::NONBLOCKING_FAILED,
                            "message"    => "fooBaz:out\n\nfooBaz:err",
                        ],
                    ],
                    // TODO
                    // order is "forced" by sleep values - easier for now
                    // otherwise having a "deterministic" output on parallel
                    // processes becomes really complicated
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Task 1/3: [Scheduling] ExternalTestTask (foo) (stage 0)
Task 2/3: [Scheduling] ExternalTestTask (fooBar) (stage 0)
Task 3/3: [Scheduling] ExternalTestTask (fooBaz) (stage 0)
Task 1/3: [Running] ExternalTestTask (foo) (stage 0)
Task 2/3: [Running] ExternalTestTask (fooBar) (stage 0)
Task 3/3: [Running] ExternalTestTask (fooBaz) (stage 0)
Task 1/3: [Success] ExternalTestTask (foo) (stage 0) ✔
Task 2/3: [Failed] ExternalTestTask (fooBar) (stage 0) ✘
Task 3/3: [Failed] ExternalTestTask (fooBaz) (stage 0) ✘
fooBaz:out

fooBaz:err
fooBar:out

fooBar:err

RESULT
                    ,
                ],
            ],
            "parallel with long(er) running processes" => [
                "config"   => [
                    "parameters" => [
                        "ascii"                  => null,
                        "hide_circumvention_tip" => true,
                    ],
                ],
                "taskData" => [
                    [
                        new ExternalTestTask("foo", 0, null, null, 500),
                    ],
                    [
                        new ExternalTestTask("fooBar", 1, "fooBar:out", "fooBar:err", 0),
                    ],
                ],
                "context"  => [
                    "parallelOptions" => new ParallelOptions(0, 2),
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => 0,
                            "message"    => "",
                        ],
                        "fooBar" => [
                            "resultCode" => TaskResult::FAILED,
                            "message"    => "fooBar:out\n\nfooBar:err",
                        ],
                    ],
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Task 1/2: [Scheduling] ExternalTestTask (foo) (stage 0)
Task 2/2: [Scheduling] ExternalTestTask (fooBar) (stage 0)
Task 1/2: [Running] ExternalTestTask (foo) (stage 0)
Task 2/2: [Running] ExternalTestTask (fooBar) (stage 0)
Task 2/2: [Failed] ExternalTestTask (fooBar) (stage 0) ✘
Task 1/2: [Success] ExternalTestTask (foo) (stage 0) ✔
fooBar:out

fooBar:err

RESULT
                    ,
                ],
            ],
        ];
    }
}
