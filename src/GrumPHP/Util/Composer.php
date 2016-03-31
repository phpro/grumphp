<?php

namespace GrumPHP\Util;

use Composer\Factory;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\JsonLoader;
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
     * @param  string|JsonFile
     *
     * @return \Composer\Package\PackageInterface
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public static function loadPackageFromJson($json)
    {
        try {
            $loader = new JsonLoader(new ArrayLoader());
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
