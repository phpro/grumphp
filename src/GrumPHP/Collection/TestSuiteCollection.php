<?php

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Exception\InvalidArgumentException;
use GrumPHP\TestSuite\TestSuiteInterface;

/**
 * Class TestSuiteCollection
 *
 * @package GrumPHP\Collection
 */
class TestSuiteCollection extends ArrayCollection
{

    /**
     * @param string $name
     *
     * @return TestSuiteInterface
     * @throws \GrumPHP\Exception\InvalidArgumentException
     */
    public function getRequired($name)
    {
        if (!$this->containsKey($name)) {
            throw InvalidArgumentException::unknownTestSuite($name);
        }

        return $this->get($name);
    }

    /**
     * @param string $name
     *
     * @return TestSuiteInterface|null
     */
    public function getOptional($name)
    {
        return $this->get($name);
    }
}
