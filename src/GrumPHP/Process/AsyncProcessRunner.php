<?php

namespace GrumPHP\Process;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Process\Process;

class AsyncProcessRunner
{
    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var array
     */
    private $processes;

    /**
     * @var int
     */
    private $running;

    /**
     * AsyncProcessRunner constructor.
     *
     * @param GrumPHP $config
     */
    public function __construct(GrumPHP $config)
    {
        $this->config = $config;
    }

    /**
     * @param Process[] $processes
     */
    public function run(array $processes)
    {
        $this->processes = $processes;
        $this->running = 0;
        $sleepDuration = $this->config->getProcessAsyncWaitTime();

        while ($this->watchProcesses()) {
            usleep($sleepDuration);
        }
    }

    /**
     * @return bool
     */
    private function watchProcesses()
    {
        foreach ($this->processes as $key => $process) {
            $isTerminated = $this->handleProcess($process);

            if ($isTerminated) {
                unset($this->processes[$key]);
            }
        }

        return count($this->processes) !== 0;
    }

    /**
     * @return bool
     */
    private function handleProcess(Process $process)
    {
        if ($process->isStarted()) {
            if ($process->isTerminated()) {
                $this->running--;
                return true;
            }

            return false;
        }

        // Only start a new process if we haven't reached the limit yet.
        if ($this->running < $this->config->getProcessAsyncLimit()) {
            $process->start();
            $this->running++;
        }

        return false;
    }
}
