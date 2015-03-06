<?php

namespace GrumPHP\Configuration;

use Zend\Stdlib\AbstractOptions;

/**
 * Class Phpcs
 *
 * @package GrumPHP\Configuration
 */
class Phpcs extends AbstractOptions
{

    /**
     * @var string
     */
    protected $standard = 'PSR2';

    /**
     * @return string
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * @param string $standard
     */
    public function setStandard($standard)
    {
        // TODO: add validation of standard
        $this->standard = $standard;
    }

}
