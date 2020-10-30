<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Ci;

use OndraM\CiDetector\CiDetector as RealCiDetector;

class CiDetector
{
    /**
     * @var RealCiDetector
     */
    private $ciDetector;

    /**
     * @var ?bool
     */
    private $ciDetected;

    public function __construct(RealCiDetector $ciDetector)
    {
        $this->ciDetector = $ciDetector;
    }

    public function isCiDetected(): bool
    {
        if (null === $this->ciDetected) {
            $this->ciDetected = $this->ciDetector->isCiDetected();
        }

        return $this->ciDetected;
    }
}
