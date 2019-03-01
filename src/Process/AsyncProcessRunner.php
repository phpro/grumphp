<?php

declare(strict_types=1);

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

    private function watchProcesses(): bool
    {
        foreach ($this->processes as $key => $process) {
            $isTerminated = $this->handleProcess($process);

            if ($isTerminated) {
                unset($this->processes[$key]);
            }
        }

        return 0 !== \count($this->processes);
    }

    private function handleProcess(Process $process): bool
    {
        if ($process->isStarted()) {
            if ($process->isTerminated()) {
                --$this->running;

                return true;
            }

            return false;
        }

        // Only start a new process if we haven't reached the limit yet.
        if ($this->running < $this->config->getProcessAsyncLimit()) {
            $process->start();
            ++$this->running;
        }

        return false;
    }
}
