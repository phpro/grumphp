<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Model;

class RunnerConfig
{
    /**
     * @var bool
     */
    private $stopOnFailure;

    /**
     * @var bool
     */
    private $hideCircumventionTip;

    /**
     * @var string|null
     */
    private $additionalInfo;

    public function __construct(
        bool $stopOnFailure,
        bool $hideCircumventionTip,
        ?string $additionalInfo
    ) {

        $this->stopOnFailure = $stopOnFailure;
        $this->hideCircumventionTip = $hideCircumventionTip;
        $this->additionalInfo = $additionalInfo;
    }

    public function stopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function hideCircumventionTip(): bool
    {
        return $this->hideCircumventionTip;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }
}
