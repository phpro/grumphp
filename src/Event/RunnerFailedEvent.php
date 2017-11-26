<?php declare(strict_types=1);

namespace GrumPHP\Event;

class RunnerFailedEvent extends RunnerEvent
{
    /**
     * @return array
     */
    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->getTaskResults() as $taskResult) {
            if (null !== $taskResult->getMessage()) {
                $messages[] = $taskResult->getMessage();
            }
        }

        return $messages;
    }
}
