<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;
use GrumPHP\TestSuite\TestSuiteInterface;

class TestSuiteCollection extends ArrayCollection
{
    /**
     * @throws \GrumPHP\Exception\InvalidArgumentException
     */
    public function getRequired(string $name): TestSuiteInterface
    {
        if (!$this->containsKey($name)) {
            throw InvalidArgumentException::unknownTestSuite($name);
        }

        return $this->get($name);
    }

    /**
     * @return TestSuiteInterface|null
     */
    public function getOptional(string $name)
    {
        return $this->get($name);
    }
}
