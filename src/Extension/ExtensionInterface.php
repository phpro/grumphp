<?php

namespace GrumPHP\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface ExtensionInterface is used for GrumPHP extensions to interface
 * with GrumPHP through the service container
 */
interface ExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function load(ContainerBuilder $container);
}
