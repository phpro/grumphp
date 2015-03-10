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

    // TODO: this pattern must exclude specs and tests as we don't really want to force anything for those
    const PATTERN_PHP = '/(.*)\.php$/i';

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->status = $this->repository->getStatus();
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $pattern
     *
     * @return array
     */
    public function locate($pattern = self::PATTERN_ALL)
    {
        $status = $this->getStatus();
        $status->all();

        $ignoredStatuses = array(StatusFile::UNTRACKED, StatusFile::DELETED);

        /** @var StatusFile $file */
        $files = array();
        foreach ($status->all() as $file) {

            // Skip untracked and deleted files:
            if (in_array($file->getType(), $ignoredStatuses)) {
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
