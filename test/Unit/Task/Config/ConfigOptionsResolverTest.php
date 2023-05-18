<?php
declare(strict_types=1);

namespace GrumPHPTest\Unit\Task\Config;

use GrumPHP\Task\Config\ConfigOptionsResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigOptionsResolverTest extends TestCase
{
    /** @test */
    public function it_can_resolve_from_closure(): void
    {
        $resolver = ConfigOptionsResolver::fromClosure(static fn (array $data): array  => [...$data, 'world']);

        self::assertSame(['hello', 'world'], $resolver->resolve(['hello']));
    }

    /** @test */
    public function it_can_resolve_from_symfony_options_resolver(): void
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setRequired('hello');

        $resolver = ConfigOptionsResolver::fromOptionsResolver($optionsResolver);

        self::assertSame(['hello' => 'world'], $resolver->resolve(['hello' => 'world']));
    }
}
