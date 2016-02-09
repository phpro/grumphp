<?php

namespace GrumPHP\IO;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleIO
 *
 * @package GrumPHP\IO
 */
class ConsoleIO implements IOInterface
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * ConsoleIO constructor.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = true)
    {
        $this->doWrite($messages, $newline, false);
    }

    /**
     * {@inheritDoc}
     */
    public function writeError($messages, $newline = true)
    {
        $this->doWrite($messages, $newline, true);
    }

    /**
     * @param array $messages
     * @param bool  $newline
     * @param bool  $stderr
     */
    private function doWrite($messages, $newline, $stderr)
    {
        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline);

            return;
        }

        $this->output->write($messages, $newline);
    }
}
