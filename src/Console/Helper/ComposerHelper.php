<?php

declare(strict_types=1);

namespace GrumPHP\Console\Helper;

use Composer\Config;
use Composer\Package\RootPackageInterface;
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
     * @var RootPackageInterface
     */
    private $rootPackage;

    /**
     * @var Config
     */
    private $configuration;

    /**
     * ComposerHelper constructor.
     *
     * @param Config|null               $configuration
     * @param RootPackageInterface|null $rootPackage
     */
    public function __construct(Config $configuration = null, RootPackageInterface $rootPackage = null)
    {
        $this->rootPackage = $rootPackage;
        $this->configuration = $configuration;
    }

    /**
     * @return RootPackageInterface|null
     */
    public function getRootPackage()
    {
        return $this->rootPackage;
    }

    public function hasRootPackage(): bool
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

    public function hasConfiguration(): bool
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
