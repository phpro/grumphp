<?php

namespace GrumPHP\Exception;

use RuntimeException as BaseRuntimeException;

/**
 * Class RuntimeException
 *
 * @package GrumPHP\Exception
 */
class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{

    /**
     * @param \Exception $e
     *
     * @return RuntimeException
     */
    public static function fromAnyException(\Exception $e)
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
