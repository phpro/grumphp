<?php
declare(strict_types=1);

namespace GrumPHP\Task\Config;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfigOptionsResolver
{
    /**
     * @var \Closure(array): array
     */
    private \Closure $resolver;

    /**
     * @param \Closure(array): array $resolver
     */
    private function __construct(\Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function fromOptionsResolver(OptionsResolver $optionsResolver): self
    {
        return self::fromClosure(
            static fn (array $options): array => $optionsResolver->resolve($options)
        );
    }

    /**
     * @param \Closure(array): array $closure
     */
    public static function fromClosure(\Closure $closure): self
    {
        return new self($closure);
    }

    public function resolve(array $data): array
    {
        return ($this->resolver)($data);
    }
}
