<?php declare(strict_types=1);

namespace GrumPHP\TestSuite;

interface TestSuiteInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getTaskNames(): array;
}
