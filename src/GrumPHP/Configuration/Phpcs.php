<?php

namespace GrumPHP\Configuration;

/**
 * Phpcs configuration
 */
class Phpcs extends AbstractConfiguration
{
    /**
     * @var string
     */
    protected $standard;

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
