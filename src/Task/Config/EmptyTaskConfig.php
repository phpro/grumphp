<?php

declare(strict_types=1);

namespace GrumPHP\Task\Config;

class EmptyTaskConfig implements TaskConfigInterface
{
    public function getName(): string
    {
        return '';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getMetadata(): Metadata
    {
        return new Metadata([]);
    }
}
