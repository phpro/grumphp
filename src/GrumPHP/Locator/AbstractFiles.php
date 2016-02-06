<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use Psr\Log\LoggerInterface;

/**
 * Class Git
 *
 * @package GrumPHP\Locator
 */
abstract class AbstractFiles
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Repository $repository
     * @param Psr\Log\LoggerInterface $logger Compatible PSR-3 logger
     */
    public function __construct(Repository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger     = $logger;
    }

    /**
     * Sets a PSR-3 logger
     * @return $this
     */
    public function setLogger()
    {
        $this->repository->setLogger($this->logger);

        return $this;
    }
}
