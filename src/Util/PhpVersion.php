<?php declare(strict_types=1);

namespace GrumPHP\Util;

use DateTime;

class PhpVersion
{
    /**
     * @var array
     */
    private $versions;

    /**
     * PhpVersion constructor.
     * @param array $versions
     */
    public function __construct(array $versions)
    {
        $this->versions = $versions;
    }

    /**
     * Check if the current version is supported
     * @param string $currentVersion
     * @return bool|null
     * @see https://secure.php.net/supported-versions.php for a list of currently supported versions
     */
    public function isSupportedVersion(string $currentVersion)
    {
        $versionIsSupported = false;
        $now = new DateTime();
        foreach ($this->versions as $number => $eol) {
            $eol = new DateTime($eol);
            if ($now < $eol && version_compare($currentVersion, $number) >= 0) {
                $versionIsSupported = true;
            }
        }

        return $versionIsSupported;
    }

    /**
     * Check if the project version is higher or equal to the given current version
     * @param string $currentVersion
     * @param string $projectVersion
     * @return bool
     */
    public function isSupportedProjectVersion(string $currentVersion, string $projectVersion): bool
    {
        return version_compare($currentVersion, $projectVersion) >= 0;
    }
}
