<?php

namespace GrumPHP\Event;

/**
 * Class RunnerFailedEvent
 *
 * @package GrumPHP\Event
 */
class RunnerFailedEvent extends RunnerEvent
{
    /**
     * @return array
     */
    public function getMessages()
    {
        $messages = array();

        foreach ($this->getTaskResults() as $taskResult) {
            if (null !== $taskResult->getMessage()) {
                $messages[] = $taskResult->getMessage();
            }
        }

        return $messages;
    }
}
