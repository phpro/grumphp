<?php

namespace GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;
use Symfony\Component\Finder\Finder;

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
     * @var Finder
     */
    protected $finder;

    /**
     * @param Repository $repository
     * @param Finder $finder
     */
    public function __construct(Repository $repository, Finder $finder)
    {
        $this->repository = $repository;
        $this->status = $this->repository->getStatus();
        $this->finder = $finder;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Finder
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

        // Return a new finder object to make it stateless:
        return $this->finder->create()->append($files);
    }
}
