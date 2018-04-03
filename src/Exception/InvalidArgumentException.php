<?php declare(strict_types=1);

namespace GrumPHP\Exception;

class InvalidArgumentException extends RuntimeException
{
    /**
     * @param $testSuiteName
     *
     * @return InvalidArgumentException
     */
    public static function unknownTestSuite($testSuiteName)
    {
        return new self(sprintf('Unknown testsuite specified: %s', $testSuiteName));
    }
}
