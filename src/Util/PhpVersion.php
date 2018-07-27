<?php

declare(strict_types=1);

namespace GrumPHP\Util;

class PhpVersion
{
    private $versions;

    public function __construct(array $versions)
    {
        $this->versions = $versions;
    }

    /**
     * @see https://secure.php.net/supported-versions.php for a list of currently supported versions
     */
    public function isSupportedVersion(string $currentVersion): bool
    {
        $now = new \DateTime();
        foreach ($this->versions as $number => $eol) {
            $eol = new \DateTime($eol);
            if ($now < $eol && (int) version_compare($currentVersion, $number) >= 0) {
                return true;
            }
        }

        return false;
    }

    public function isSupportedProjectVersion(string $currentVersion, string $projectVersion): bool
    {
        return (int) version_compare($currentVersion, $projectVersion) >= 0;
    }
}
