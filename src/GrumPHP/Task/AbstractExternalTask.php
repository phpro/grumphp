<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\LocatorInterface;
use Symfony\Component\Process\ProcessBuilder;

abstract class AbstractExternalTask implements ExternalTaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var LocatorInterface
     */
    protected $externalCommandLocator;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @param GrumPHP $grumPHP
     * @param LocatorInterface $externalCommandLocator
     * @param ProcessBuilder $processBuilder
     */
    public function __construct(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        $this->grumPHP = $grumPHP;
        $this->externalCommandLocator = $externalCommandLocator;
        $this->processBuilder = $processBuilder;
    }
}
