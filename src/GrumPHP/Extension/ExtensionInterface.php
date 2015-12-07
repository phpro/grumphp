<?php

namespace GrumPHP\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface ExtensionInterface is used for GrumPHP extensions to interface
 * with GrumPHP through the service container
 */
interface ExtensionInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function load(ContainerInterface $container);
}
