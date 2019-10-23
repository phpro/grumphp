<?php

declare(strict_types=1);

namespace GrumPHP\Task\Config;

class TaskConfig implements TaskConfigInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * @var Metadata
     */
    private $metadata;

    public function __construct(string $name, array $options, Metadata $metadata)
    {
        $this->name = $name;
        $this->options = $options;
        $this->metadata = $metadata;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}
