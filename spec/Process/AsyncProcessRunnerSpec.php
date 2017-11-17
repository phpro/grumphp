<?php

namespace spec\GrumPHP\Process;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\AsyncProcessRunner;
use PhpSpec\ObjectBehavior;
use Prophecy\Prophet;
use Symfony\Component\Process\Process;

class AsyncProcessRunnerSpec extends ObjectBehavior
{
    public function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);

        $grumPHP->getProcessAsyncWaitTime()->willReturn(0);
        $grumPHP->getProcessAsyncLimit()->willReturn(5);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AsyncProcessRunner::class);
    }

    function it_should_be_able_to_run_processes()
    {
        $prophet = new Prophet();
        $processes = [];

        for ($i = 0; $i < 20; $i++) {
            $process = $prophet->prophesize(Process::class);

            $process->started = false;
            $process->terminated = false;

            $process->start()->will(function () use ($process) {
                $process->started = true;
            })->shouldBeCalledTimes(1);

            $process->isTerminated()->will(function () use ($process) {
                if (!$process->terminated) {
                    $process->terminated = true;
                    return false;
                }

                return true;
            })->shouldBeCalledTimes(2);

            // The number of times isStarted() is called starts at 3
            // and increases by 2 after each chunk of five processes.
            $process->isStarted()->will(function () use ($process) {
                return $process->started;
            })->shouldBeCalledTimes(floor($i / 5) * 2 + 3);

            $processes[] = $process->reveal();
        }

        $this->run($processes);
        $prophet->checkPredictions();
    }
}
