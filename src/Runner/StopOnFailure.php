<?php
declare(strict_types=1);

namespace GrumPHP\Runner;

use Amp\Cancellation;
use Amp\DeferredCancellation;
use GrumPHP\Configuration\Model\RunnerConfig;

final class StopOnFailure
{
    private function __construct(
        private DeferredCancellation $cancellation,
        private bool $enabled
    ) {
    }

    public static function dummy(): self
    {
        return new self(
            new DeferredCancellation(),
            false
        );
    }

    public static function createFromConfig(RunnerConfig $config): self
    {
        return new self(
            new DeferredCancellation(),
            $config->stopOnFailure()
        );
    }

    public function cancellation(): Cancellation
    {
        return $this->cancellation->getCancellation();
    }

    public function decideForResult(TaskResultInterface $result): void
    {
        if ($result->hasFailed() && $result->isBlocking()) {
            $this->stop();
        }
    }

    public function stop(): void
    {
        if ($this->enabled) {
            $this->cancellation->cancel();
        }
    }
}
