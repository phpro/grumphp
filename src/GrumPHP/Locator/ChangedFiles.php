<?php

namespace GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;
use GrumPHP\Collection\FilesCollection;

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
     * Statuses that won't be located:
     *
     * @var array
     */
    protected static $ignoredStatuses = array(
        StatusFile::UNTRACKED,
        StatusFile::DELETED
    );

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
     * @return FilesCollection
     */
    public function locate()
    {
        /** @var StatusFile $file */
        $files = array();
        foreach ($this->getStatus()->all() as $file) {
            // Skip untracked and deleted files:
            if (in_array($file->getType(), self::$ignoredStatuses)) {
                continue;
            }

            $files[] = new \SplFileInfo($file->getName());
        }

        return new FilesCollection($files);
    }
}
