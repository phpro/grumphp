<?php

namespace GrumPHP\Util;

use Composer\Config;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\Loader\RootPackageLoader;
use Composer\Package\Loader\JsonLoader;
use Composer\Repository\RepositoryFactory;
use Exception;
use GrumPHP\Exception\RuntimeException;

/**
 * Class Composer
 *
 * @package GrumPHP\Util
 */
class Composer
{

    /**
     * @param string|JsonFile $json
     * @param Config $config
     *
     * @return \Composer\Package\RootPackageInterface
     */
    public static function loadRootPackageFromJson($json, Config $config = null)
    {
        try {
            $config = (null !== $config) ? $config : self::loadConfiguration();
            $loader = new JsonLoader(new RootPackageLoader(
                RepositoryFactory::manager(new NullIO(), $config),
                $config
            ));
            $package = $loader->load($json);
        } catch (Exception $e) {
            throw RuntimeException::fromAnyException($e);
        }

        return $package;
    }

    /**
     * @return \Composer\Config
     */
    public static function loadConfiguration()
    {
        try {
            $configuration = Factory::createConfig();
        } catch (Exception $e) {
            throw RuntimeException::fromAnyException($e);
        }

        return $configuration;
    }
}
