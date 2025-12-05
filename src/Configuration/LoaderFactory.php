<?php
declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Configuration\Loader\DistFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class LoaderFactory
{
    private const ENV = 'grumphp';

    /**
     * @psalm-suppress DeprecatedClass - XmlFileLoader is deprecated but still in use here for backwards compatibility.
     *
     * @param list<string> $paths
     */
    public static function createLoader(ContainerBuilder $container, array $paths = []): DelegatingLoader
    {
        $locator = new FileLocator($paths);

        /** @Deprecated - Remove in a future version of PHP where SF > 7.4 */
        $xmlLoader = class_exists(XmlFileLoader::class)
            ? new XmlFileLoader($container, $locator, self::ENV)
            : null;

        $resolver = new LoaderResolver(array_filter([
            $xmlLoader,
            $yamlLoader = new YamlFileLoader($container, $locator, self::ENV),
            $iniLoader = new IniFileLoader($container, $locator, self::ENV),
            new GlobFileLoader($container, $locator, self::ENV),
            new DirectoryLoader($container, $locator, self::ENV),
            $xmlLoader ? new DistFileLoader($xmlLoader) : null,
            new DistFileLoader($yamlLoader),
            new DistFileLoader($iniLoader),
        ]));

        return new DelegatingLoader($resolver);
    }
}
