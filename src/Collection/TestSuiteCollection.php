<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;
use GrumPHP\TestSuite\TestSuiteInterface;

class TestSuiteCollection extends ArrayCollection
{
    public function getRequired(string $name): TestSuiteInterface
    {
        if (!$this->containsKey($name)) {
            throw InvalidArgumentException::unknownTestSuite($name);
        }

        return $this->get($name);
    }

    public function getOptional(string $name): ?TestSuiteInterface
    {
        return $this->get($name);
    }
}
