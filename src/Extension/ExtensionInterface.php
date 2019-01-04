<?php

declare(strict_types=1);

namespace GrumPHP\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface ExtensionInterface is used for GrumPHP extensions to interface
 * with GrumPHP through the service container.
 */
interface ExtensionInterface
{
    public function load(ContainerBuilder $container);
}
