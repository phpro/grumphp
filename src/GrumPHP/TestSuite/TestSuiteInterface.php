<?php

namespace GrumPHP\TestSuite;

/**
 * Class TestSuite
 *
 * @package GrumPHP\TestSuite
 */
interface TestSuiteInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getTaskNames();
}
