<?php declare(strict_types=1);

namespace GrumPHP\Util;

use Composer\Config;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Package\Loader\JsonLoader;
use Composer\Package\Loader\RootPackageLoader;
use Composer\Repository\RepositoryFactory;
use GrumPHP\Exception\RuntimeException;
use Throwable;

class Composer
{
    /**
     * @param string|JsonFile $json
     */
    public static function loadRootPackageFromJson($json, Config $config = null): \Composer\Package\RootPackageInterface
    {
        try {
            $config = (null !== $config) ? $config : self::loadConfiguration();
            $loader = new JsonLoader(new RootPackageLoader(
                RepositoryFactory::manager(new NullIO(), $config),
                $config
            ));
            $package = $loader->load($json);
        } catch (Throwable $e) {
            throw RuntimeException::fromAnyException($e);
        }

        return $package;
    }

    public static function loadConfiguration(): \Composer\Config
    {
        try {
            $configuration = Factory::createConfig();
        } catch (Throwable $e) {
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
     */
    public static function ensureProjectBinDirInSystemPath(string $binDir)
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
