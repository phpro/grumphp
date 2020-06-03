<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

/**
 * @psalm-immutable
 */
class ProcessConfig
{
    /**
     * @var float|null
     */
    private $timeout;

    public function __construct(
        ?float $timeout
    ) {
        $this->timeout = $timeout;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }
}
