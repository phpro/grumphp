<?php

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Turns default private services into public services via a compiler pass,
 * instead of modifying all service definition across several files.
 *
 * This mitigates the issues where fetching services out of the container in
 * Symfony 3.4 / 4.0 will trigger an error, because they now get declared as
 * a "private" service by default.
 */
class PublicServiceVisibilityCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // We only want to target Symfony DependencyInjection >= 3.2
        if (!$this->hasPrivateServiceDeclarationsByDefault()) {
            return;
        }

        foreach ($container->getDefinitions() as $definition) {
            $changes = $definition->getChanges();

            if (!isset($changes['public'])) {
                $definition->setPublic(true);
                $definition->setChanges($changes);
            }
        }
    }

    /**
     * Check if services in the container are marked as "private" by default.
     * This will be the case, starting from Symfony 3.4 and up.
     *
     * @return bool whether services get defined as "private" by default.
     *
     * @link https://github.com/symfony/symfony/pull/24238
     */
    private function hasPrivateServiceDeclarationsByDefault()
    {
        // The EnvVarProcessor interface got introduced in Symfony 3.4.
        return interface_exists('Symfony\Component\DependencyInjection\EnvVarProcessorInterface');
    }
}
