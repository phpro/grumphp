<?php

namespace GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;
use GrumPHP\Finder\FinderFactory;
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
     * @var FinderFactory
     */
    protected $finderFactory;

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
     * @param FinderFactory $finderFactory
     */
    public function __construct(Repository $repository, FinderFactory $finderFactory)
    {
        $this->repository = $repository;
        $this->status = $this->repository->getStatus();
        $this->finderFactory = $finderFactory;
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


        /** @var StatusFile $file */
        $files = array();
        foreach ($this->getStatus()->all() as $file) {
            // Skip untracked and deleted files:
            if (in_array($file->getType(), self::$ignoredStatuses)) {
                continue;
            }

            if ($file->getType() === StatusFile::RENAMED) {
                die('renamed');
                var_dump($file);exit;
            }

            $files[] = $file->getName();
        }

        return $this->finderFactory->create($files);
    }
}
