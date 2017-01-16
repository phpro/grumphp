<?php

namespace GrumPHP\Exception;

use Exception;
use GrumPHP\Task\TaskInterface;
use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
    /**
     * @param Exception $e
     *
     * @return RuntimeException
     */
    public static function fromAnyException(Exception $e)
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @param TaskInterface $task
     *
     * @return RuntimeException
     */
    public static function invalidTaskReturnType(TaskInterface $task)
    {
        return new self(sprintf('The %s task did not return a TaskResult.', $task->getName()));
    }
}
