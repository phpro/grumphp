<?php
declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass as SymfonyRegisterListenersPass;

/**
 * @see https://github.com/symfony/symfony/pull/40468
 * Symfony removed the ability to add custom tags. So we need to take care of that!
 */
final class RegisterListenersPass implements CompilerPassInterface
{
    private SymfonyRegisterListenersPass $pass;

    public function __construct(SymfonyRegisterListenersPass $pass)
    {
        $this->pass = $pass;
    }

    public static function create(): self
    {
        return new self(new SymfonyRegisterListenersPass());
    }

    public function process(ContainerBuilder $container): void
    {
        $this->changeKey($container, 'grumphp.event_listener', 'kernel.event_listener');
        $this->changeKey($container, 'grumphp.event_subscriber', 'kernel.event_subscriber');

        $this->pass->process($container);
    }

    private function changeKey(ContainerBuilder $container, string $sourceKey, string $targetKey): void
    {
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->hasTag($sourceKey)) {
                $attributes = $definition->getTag($sourceKey)[0];
                $definition->addTag($targetKey, $attributes);
                $definition->clearTag($sourceKey);
            }
        }
    }
}
