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
     * @var array
     */
    protected $configuration;

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
     * @param array $configuration
     * @param LocatorInterface $externalCommandLocator
     * @param ProcessBuilder $processBuilder
     */
    public function __construct(
        GrumPHP $grumPHP,
        array $configuration,
        LocatorInterface $externalCommandLocator,
        ProcessBuilder $processBuilder
    ) {
        $this->grumPHP = $grumPHP;
        $this->configuration = array_merge($this->getDefaultConfiguration(), $configuration);
        $this->externalCommandLocator = $externalCommandLocator;
        $this->processBuilder = $processBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
