<?php

declare(strict_types=1);

namespace GrumPHP\Task\Config;

class LazyTaskConfig implements TaskConfigInterface
{
    /**
     * @var callable() : TaskConfigInterface
     */
    private $configFactory;

    /**
     * @var null|TaskConfigInterface
     */
    private $config;

    /**
     * @param callable() : TaskConfigInterface $configFactory
     */
    public function __construct(callable $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public function getName(): string
    {
        return $this->proxy()->getName();
    }

    public function getOptions(): array
    {
        return $this->proxy()->getOptions();
    }

    public function getMetadata(): Metadata
    {
        return $this->proxy()->getMetadata();
    }

    private function proxy(): TaskConfigInterface
    {
        if (!$this->config) {
            $this->config = ($this->configFactory)();
        }

        return $this->config;
    }
}
