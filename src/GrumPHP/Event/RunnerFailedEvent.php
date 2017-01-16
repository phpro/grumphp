<?php

namespace GrumPHP\Event;

class RunnerFailedEvent extends RunnerEvent
{
    /**
     * @return array
     */
    public function getMessages()
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
