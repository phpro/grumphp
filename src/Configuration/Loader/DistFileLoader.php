<?php
declare(strict_types=1);

namespace GrumPHP\Configuration\Loader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

/**
 * A decorating dist loader that supports **.dist files and defers loading.
 */
final class DistFileLoader implements LoaderInterface
{
    private LoaderInterface $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function load(mixed $resource, string $type = null): mixed
    {
        return $this->loader->load($resource, $type);
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        if (!\is_string($resource)) {
            return false;
        }

        if ($type !== null) {
            return $this->loader->supports($resource, $type);
        }

        $extension = pathinfo($resource, \PATHINFO_EXTENSION);
        if ($extension !== 'dist') {
            return false;
        }

        $distForFile = pathinfo($resource, \PATHINFO_FILENAME);

        return $this->loader->supports($distForFile);
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->loader->getResolver();
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->loader->setResolver($resolver);
    }
}
