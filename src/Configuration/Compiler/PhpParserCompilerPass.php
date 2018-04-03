<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class PhpParserCompilerPass implements CompilerPassInterface
{
    const TAG = 'php_parser.visitor';

    /**
     * Sets the visitors as non shared services.
     * This will make sure that the state of the visitor won't need to be reset after an iteration of the traverser.
     *
     * All visitor Ids are registered in the traverser configurator.
     * The configurator will be used to apply the configured visitors to the traverser.
     *
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $traverserConfigurator = $container->findDefinition('grumphp.parser.php.configurator.traverser');
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            $definition = $container->findDefinition($id);
            $this->markServiceAsPrototype($definition);
            foreach ($tags as $tag) {
                $alias = array_key_exists('alias', $tag) ? $tag['alias'] : $id;
                $traverserConfigurator->addMethodCall('registerVisitorId', [$alias, $id]);
            }
        }
    }

    /**
     * This method can be used to make the service shared cross-version.
     * From Symfony 2.8 the setShared method was available.
     * The 2.7 version is the LTS, so we still need to support it.
     *
     * @see http://symfony.com/blog/new-in-symfony-3-1-customizable-yaml-parsing-and-dumping
     *
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function markServiceAsPrototype(Definition $definition)
    {
        if (method_exists($definition, 'setShared')) {
            $definition->setShared(false);

            return;
        }

        if (method_exists($definition, 'setScope')) {
            $definition->setScope('prototype');

            return;
        }

        throw new RuntimeException('The visitor could not be marked as unshared');
    }
}
