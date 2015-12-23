<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Process\ProcessBuilder;

/**
 * Class AbstractLinter
 *
 * @package GrumPHP\Task
 */
abstract class AbstractLinterTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var LinterInterface
     */
    protected $linter;

    /**
     * @param GrumPHP         $grumPHP
     * @param LinterInterface $linter
     */
    public function __construct(GrumPHP $grumPHP, LinterInterface $linter)
    {
        $this->grumPHP = $grumPHP;
        $this->linter = $linter;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * Validates if the linter is installed.
     *
     * @throws RuntimeException
     */
    public function guardLinterIsInstalled()
    {
        if (!$this->linter->isInstalled()) {
            throw new RuntimeException(
                sprintf('The %s can\'t run on your system. Please install all dependencies.', $this->getName())
            );
        }
    }
}
