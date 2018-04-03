<?php

declare(strict_types=1);

namespace GrumPHP\TestSuite;

interface TestSuiteInterface
{
    public function getName(): string;

    public function getTaskNames(): array;
}
