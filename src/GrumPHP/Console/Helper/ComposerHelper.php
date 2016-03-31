<?php

namespace GrumPHP\Console\Helper;

use Composer\Config;
use Composer\Package\RootPackage;
use Symfony\Component\Console\Helper\Helper;

/**
 * This class will make the composer configurationa available for the commands.
 *
 * Class ComposerHelper
 */
class ComposerHelper extends Helper
{
    const HELPER_NAME = 'composer';

    /**
     * @var RootPackage
     */
    private $rootPackage;

    /**
     * @var Config
     */
    private $configuration;

    /**
     * ComposerHelper constructor.
     *
     * @param Config|null      $configuration
     * @param RootPackage|null $rootPackage
     */
    public function __construct(Config $configuration = null, RootPackage $rootPackage = null)
    {
        $this->rootPackage = $rootPackage;
        $this->configuration = $configuration;
    }

    /**
     * @return RootPackage|null
     */
    public function getRootPackage()
    {
        return $this->rootPackage;
    }

    /**
     * @return bool
     */
    public function hasRootPackage()
    {
        return null !== $this->rootPackage;
    }

    /**
     * @return Config|null
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return bool
     */
    public function hasConfiguration()
    {
        return null !== $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
