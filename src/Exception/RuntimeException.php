<?php declare(strict_types=1);

namespace GrumPHP\Exception;

use Exception;
use GrumPHP\Task\TaskInterface;
use RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
    /**
     *
     * @return RuntimeException
     */
    public static function fromAnyException(Exception $e): RuntimeException
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    /**
     *
     * @return RuntimeException
     */
    public static function invalidTaskReturnType(TaskInterface $task): RuntimeException
    {
        return new self(sprintf('The %s task did not return a TaskResult.', $task->getName()));
    }
}
