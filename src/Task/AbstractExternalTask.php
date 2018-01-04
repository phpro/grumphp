<?php declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;

abstract class AbstractExternalTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var ProcessFormatterInterface
     */
    protected $formatter;

    public function __construct(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $this->grumPHP = $grumPHP;
        $this->processBuilder = $processBuilder;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }
}
