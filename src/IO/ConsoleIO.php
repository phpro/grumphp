<?php

declare(strict_types=1);

namespace GrumPHP\IO;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleIO implements IOInterface, \Serializable
{
    private $input;
    private $output;

    /**
     * @var string
     */
    private $stdin = '';

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function isInteractive(): bool
    {
        return $this->input->isInteractive();
    }

    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    public function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    public function isVeryVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    public function isDebug(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }

    public function write(array $messages, bool $newline = true): void
    {
        $this->doWrite($messages, $newline, false);
    }

    public function writeError(array $messages, bool $newline = true): void
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
     * @param mixed $handle
     */
    public function readCommandInput($handle): string
    {
        if (!is_resource($handle)) {
            throw new RuntimeException(
                sprintf('Expected a resource stream for reading the commandline input. Got %s.', gettype($handle))
            );
        }

        // Validate if the stdin was already read (possibly by another part of the code)
        if ($this->stdin !== '' || ftell($handle) > 0) {
            return $this->stdin;
        }

        $input = '';

        // Validate if the resource is being piped to.
        // If it is not a tty, it can be read in a non blocking way.
        if (!\stream_isatty($handle)) {
            // Once the stream is read, it is marked as EOF.
            // From that point on, you sadly cannot use interactive cli questions anymore.
            $input = \stream_get_contents($handle) ?: '';
        }

        // When the input only consist of white space characters, we assume that there is no input.
        $this->stdin = !preg_match_all('/^([\s]*)$/', $input) ? $input : '';

        return $this->stdin;
    }

    private function doWrite(array $messages, bool $newline, bool $stderr): void
    {
        if (true === $stderr && $this->output instanceof ConsoleOutputInterface) {
            $this->output->getErrorOutput()->write($messages, $newline);

            return;
        }

        $this->output->write($messages, $newline);
    }

    public function style(): StyleInterface
    {
        return new SymfonyStyle($this->input, $this->output);
    }

    public function section(): ConsoleSectionOutput
    {
        assert($this->output instanceof ConsoleOutputInterface);
        return $this->output->section();
    }

    public function colorize(array $messages, string $color): array
    {
        return array_map(
            static function (string $message) use ($color) : string {
                return '<fg='.$color.'>'.$message.'</fg='.$color.'>';
            },
            $messages
        );
    }

    /**
     * Serializing this IO will result in an unwritable resource stream.
     * Therefor we serialize the data end build up a new stream instead.
     */
    public function serialize()
    {
        return serialize([
            'input' => [
                'arguments' => $this->input->getArguments(),
            ],
            'output' => [
                'verbosity' => $this->output->getVerbosity(),
            ],
        ]);
    }

    /**
     * Use the serialized data to rebuild new input + output streams.
     * Note: When you run in parallel mode, the stream will be non-blocking.
     * All tasks can write at the same time, which is not optimal.
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized, ['allowed_classes' => false]);

        $this->input = new ArrayInput(
            (array) ($data['input']['arguments'] ?? [])
        );
        $this->output = new ConsoleOutput(
            (int) ($data['output']['verbosity'] ?? ConsoleOutput::VERBOSITY_NORMAL)
        );
    }
}
