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
     * @return array
     */
    public function locate()
    {
        $ignoredStatuses = array(StatusFile::UNTRACKED, StatusFile::DELETED);

        /** @var StatusFile $file */
        $files = array();
        foreach ($this->getStatus()->all() as $file) {
            // Skip untracked and deleted files:
            if (in_array($file->getType(), $ignoredStatuses)) {
                continue;
            }

            $files[] = $file->getName();
        }

        return $files;
    }
}
