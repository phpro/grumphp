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

    /**
     * Composer contains some logic to prepend the current bin dir to the system PATH.
     * To make sure this application works the same in CLI and Composer modus,
     * we'll have to ensure that the bin path is always prefixed.
     *
     * @link https://github.com/composer/composer/blob/1.1/src/Composer/EventDispatcher/EventDispatcher.php#L147-L160
     *
     * @param string $binDir
     */
    public static function ensureProjectBinDirInSystemPath($binDir)
    {
        $pathStr = 'PATH';
        if (!isset($_SERVER[$pathStr]) && isset($_SERVER['Path'])) {
            $pathStr = 'Path';
        }

        if (!is_dir($binDir)) {
            return;
        }

        // add the bin dir to the PATH to make local binaries of deps usable in scripts
        $binDir = realpath($binDir);
        $hasBindDirInPath = preg_match(
            '{(^|' . PATH_SEPARATOR . ')' . preg_quote($binDir) . '($|' . PATH_SEPARATOR . ')}',
            $_SERVER[$pathStr]
        );

        if (!$hasBindDirInPath && isset($_SERVER[$pathStr])) {
            $_SERVER[$pathStr] = $binDir . PATH_SEPARATOR . getenv($pathStr);
            putenv($pathStr . '=' . $_SERVER[$pathStr]);
        }
    }
}
