<?php

declare(strict_types=1);

namespace GrumPHP\Extension;

/**
 * Registers your own GrumPHP.
 */
interface ExtensionInterface
{
    /**
     * Return a list of additional symfony/conso:e service imports that
     * GrumPHP needs to perform after loading all internal configurations.
     *
     * We support following loaders: YAML, XML, INI, GLOB, DIR
     *
     * More info
     * @link https://symfony.com/doc/current/service_container.html
     *
     * @return iterable<string>
     */
    public function imports(): iterable;
}
