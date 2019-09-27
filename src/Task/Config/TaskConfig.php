<?php

declare(strict_types=1);

namespace GrumPHP\Task\Config;

class TaskConfig
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
     * @var array
     */
    private $metadata;

    public function __construct(string $name, array $options, array $metadata)
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

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
