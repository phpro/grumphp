<?php

declare(strict_types=1);

namespace GrumPHP\Process;

use Symfony\Component\Process\Process;

class AsyncProcessRunner
{
    /**
     * @var array
     */
    private $processes;

    /**
     * @var int
     */
    private $running;

    /**
     * @var int
     */
    private $asyncWaitTime;

    /**
     * @var int
     */
    private $asyncLimit;

    /**
     * AsyncProcessRunner constructor.
     */
    public function __construct(int $asyncWaitTime, int $asyncLimit)
    {
        $this->asyncWaitTime = $asyncWaitTime;
        $this->asyncLimit = $asyncLimit;
    }

    /**
     * @param Process[] $processes
     */
    public function run(array $processes): void
    {
        $this->processes = $processes;
        $this->running = 0;

        while ($this->watchProcesses()) {
            usleep($this->asyncLimit);
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
        if ($this->running < $this->asyncLimit) {
            $process->start();
            ++$this->running;
        }

        return false;
    }
}
