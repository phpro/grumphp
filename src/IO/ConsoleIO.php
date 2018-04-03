<?php declare(strict_types=1);

namespace GrumPHP\IO;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var string
     */
    private $stdin;

    /**
     * ConsoleIO constructor.
     *
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

    
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     *
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function readCommandInput(resource $handle): string
    {
        if (!is_resource($handle)) {
            throw new RuntimeException(
                sprintf('Expected a resource stream for reading the commandline input. Got %s.', gettype($handle))
            );
        }

        if ($this->stdin !== null || ftell($handle) !== 0) {
            return $this->stdin;
        }

        $input = '';
        while (!feof($handle)) {
            $input .= fread($handle, 1024);
        }

        // When the input only consist of white space characters, we assume that there is no input.
        $this->stdin = !preg_match_all('/^([\s]*)$/', $input) ? $input : '';

        return  $this->stdin;
    }

    
    private function doWrite(array $messagesbool ,bool  $newline, $stderr)
    {
        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline);

            return;
        }

        $this->output->write($messages, $newline);
    }
}
