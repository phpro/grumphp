<?php declare(strict_types=1);

namespace GrumPHP\Exception;

use GrumPHP\Task\TaskInterface;
use RuntimeException as BaseRuntimeException;
use Throwable;

class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
    /**
     * @return RuntimeException
     */
    public static function fromAnyException(Throwable $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @return RuntimeException
     */
    public static function invalidTaskReturnType(TaskInterface $task): self
    {
        return new self(sprintf('The %s task did not return a TaskResult.', $task->getName()));
    }
}
