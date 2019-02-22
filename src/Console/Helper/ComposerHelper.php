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

    public function __construct(?Config $configuration = null, ?RootPackageInterface $rootPackage = null)
    {
        $this->rootPackage = $rootPackage;
        $this->configuration = $configuration;
    }

    public function getRootPackage(): ?RootPackageInterface
    {
        return $this->rootPackage;
    }

    public function hasRootPackage(): bool
    {
        return null !== $this->rootPackage;
    }

    public function getConfiguration(): ?Config
    {
        return $this->configuration;
    }

    public function hasConfiguration(): bool
    {
        return null !== $this->configuration;
    }

    public function getName(): string
    {
        return self::HELPER_NAME;
    }
}
