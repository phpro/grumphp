<?php declare(strict_types=1);

namespace GrumPHP\Exception;

class InvalidArgumentException extends RuntimeException
{
    /**
     * @return InvalidArgumentException
     */
    public static function unknownTestSuite($testSuiteName): InvalidArgumentException
    {
        return new self(sprintf('Unknown testsuite specified: %s', $testSuiteName));
    }
}
