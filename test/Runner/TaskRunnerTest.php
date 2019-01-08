<?php

namespace GrumPHPTest\Task;

use GrumPHP\Runner\ParallelOptions;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHPTest\Helper\ExternalParallelTestTask;
use GrumPHPTest\Helper\ExternalTestTask;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class TaskRunnerTest extends TestCase
{
    use GrumPHPTestHelperTrait;

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
                        new ExternalParallelTestTask("foo", 0),
                    ],
                    [
                        new ExternalParallelTestTask("fooBar", 1, "fooBar:out", "fooBar:err"),
                    ],
                    [
                        new ExternalParallelTestTask("fooBaz", 1, "fooBaz:out", "fooBaz:err"),
                        [
                            "blocking" => false,
                        ],
                    ],
                ],
                "context"  => [
                    "parallelOptions" => null,
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => TaskResult::PASSED,
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
Running task 1/3: ExternalParallelTestTask... ✔
Running task 2/3: ExternalParallelTestTask... ✘
Running task 3/3: ExternalParallelTestTask... ✘
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
                        new ExternalParallelTestTask("foo", 0, null, null, 0),
                    ],
                    [
                        new ExternalParallelTestTask("fooBar", 1, "fooBar:out", "fooBar:err", 500),
                    ],
                    [
                        new ExternalParallelTestTask("fooBaz", 1, "fooBaz:out", "fooBaz:err", 1000),
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
                            "resultCode" => TaskResult::PASSED,
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
Task 1/3: [Scheduling] ExternalParallelTestTask (foo)
Task 2/3: [Scheduling] ExternalParallelTestTask (fooBar)
Task 3/3: [Scheduling] ExternalParallelTestTask (fooBaz)
 >>>>> STARTING STAGE 0 <<<<< 
Task 1/3: [Running] ExternalParallelTestTask (foo)
Task 2/3: [Running] ExternalParallelTestTask (fooBar)
Task 3/3: [Running] ExternalParallelTestTask (fooBaz)
Task 1/3: [Success] ExternalParallelTestTask (foo) ✔
Task 2/3: [Failed] ExternalParallelTestTask (fooBar) ✘
Task 3/3: [Failed] ExternalParallelTestTask (fooBaz) ✘
 >>>>> FINISHING STAGE 0 <<<<< 
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
                        new ExternalParallelTestTask("foo", 0, null, null, 500),
                    ],
                    [
                        new ExternalParallelTestTask("fooBar", 1, "fooBar:out", "fooBar:err", 0),
                    ],
                ],
                "context"  => [
                    "parallelOptions" => new ParallelOptions(0, 2),
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => TaskResult::PASSED,
                            "message"    => "",
                        ],
                        "fooBar" => [
                            "resultCode" => TaskResult::FAILED,
                            "message"    => "fooBar:out\n\nfooBar:err",
                        ],
                    ],
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Task 1/2: [Scheduling] ExternalParallelTestTask (foo)
Task 2/2: [Scheduling] ExternalParallelTestTask (fooBar)
 >>>>> STARTING STAGE 0 <<<<< 
Task 1/2: [Running] ExternalParallelTestTask (foo)
Task 2/2: [Running] ExternalParallelTestTask (fooBar)
Task 2/2: [Failed] ExternalParallelTestTask (fooBar) ✘
Task 1/2: [Success] ExternalParallelTestTask (foo) ✔
 >>>>> FINISHING STAGE 0 <<<<< 
fooBar:out

fooBar:err

RESULT
                    ,
                ],
            ],
            "parallel with mixed tasks"                => [
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
                        // will run first (same prio) - even if finishes "later"
                        new ExternalParallelTestTask("fooBar", 0, null, null, 500),
                    ],
                ],
                "context"  => [
                    "parallelOptions" => new ParallelOptions(0, 2),
                ],
                "expected" => [
                    "taskResults" => [
                        "foo"    => [
                            "resultCode" => TaskResult::PASSED,
                            "message"    => "",
                        ],
                        "fooBar" => [
                            "resultCode" => TaskResult::PASSED,
                            "message"    => "",
                        ],
                    ],
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Task 1/2: [Scheduling] ExternalParallelTestTask (fooBar)
Task 2/2: [Scheduling] ExternalTestTask (foo)
 >>>>> STARTING STAGE 0 <<<<< 
Task 1/2: [Running] ExternalParallelTestTask (fooBar)
Task 1/2: [Success] ExternalParallelTestTask (fooBar) ✔
Task 2/2: [Running] ExternalTestTask (foo)
Task 2/2: [Success] ExternalTestTask (foo) ✔
 >>>>> FINISHING STAGE 0 <<<<< 

RESULT
                    ,
                ],
            ],
            "runs in stages"                => [
                "config"   => [
                    "parameters" => [
                        "ascii"                  => null,
                        "hide_circumvention_tip" => true,
                    ],
                ],
                "taskData" => [
                    [
                        new ExternalParallelTestTask("Runs in stage 1, prio 1"),
                        ["stage" => 1, "priority" => 1]
                    ],
                    [
                        new ExternalParallelTestTask("Runs in stage 1, prio 2"),
                        ["stage" => 1, "priority" => 2]
                    ],
                    [
                        new ExternalParallelTestTask("Runs in stage 2, prio 1"),
                        ["stage" => 2, "priority" => 1]
                    ],
                    [
                        new ExternalParallelTestTask("Runs in stage 2, prio 2"),
                        ["stage" => 2, "priority" => 2]
                    ],
                ],
                "context"  => [
                    "parallelOptions" => new ParallelOptions(0, 1),
                ],
                "expected" => [
                    "output"      => <<<RESULT
GrumPHP is sniffing your code!
Task 1/4: [Scheduling] ExternalParallelTestTask (Runs in stage 2, prio 2)
Task 2/4: [Scheduling] ExternalParallelTestTask (Runs in stage 2, prio 1)
Task 3/4: [Scheduling] ExternalParallelTestTask (Runs in stage 1, prio 2)
Task 4/4: [Scheduling] ExternalParallelTestTask (Runs in stage 1, prio 1)
 >>>>> STARTING STAGE 2 <<<<< 
Task 1/4: [Running] ExternalParallelTestTask (Runs in stage 2, prio 2)
Task 1/4: [Success] ExternalParallelTestTask (Runs in stage 2, prio 2) ✔
Task 2/4: [Running] ExternalParallelTestTask (Runs in stage 2, prio 1)
Task 2/4: [Success] ExternalParallelTestTask (Runs in stage 2, prio 1) ✔
 >>>>> FINISHING STAGE 2 <<<<< 
 >>>>> STARTING STAGE 1 <<<<< 
Task 3/4: [Running] ExternalParallelTestTask (Runs in stage 1, prio 2)
Task 3/4: [Success] ExternalParallelTestTask (Runs in stage 1, prio 2) ✔
Task 4/4: [Running] ExternalParallelTestTask (Runs in stage 1, prio 1)
Task 4/4: [Success] ExternalParallelTestTask (Runs in stage 1, prio 1) ✔
 >>>>> FINISHING STAGE 1 <<<<< 

RESULT
                    ,
                ],
            ],
        ];
    }
}
