<?php

namespace GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;

/**
 * Class Git
 *
 * @package GrumPHP\Locator
 */
class ChangedFiles implements LocatorInterface
{

    const PATTERN_ALL = '/(.*)/';
    const PATTERN_PHP = '/(.*)\.php$/i';

    /**
     * @var string
     */
    protected $gitDir;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @param $gitDir
     */
    public function __construct($gitDir)
    {
        $this->gitDir = $gitDir;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        if (!$this->status) {
            $repository = Repository::open($this->gitDir);
            $this->status = $repository->getStatus();
        }
        return $this->status;
    }

    /**
     * @param $pattern
     *
     * @return array|void
     */
    public function locate($pattern = self::PATTERN_ALL)
    {
        $status = $this->getStatus();
        $status->all();

        /** @var StatusFile $file */
        foreach ($status->all() as $file) {

            // Skip untracked and deleted files:
            if (in_array($file->getType(), [StatusFile::UNTRACKED, StatusFile::DELETED])) {
                continue;
            }

            // Validate path with a pattern.
            $path = $file->getName();
            if (!preg_match($pattern, $path)) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }
}
