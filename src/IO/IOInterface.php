<?php declare(strict_types=1);

namespace GrumPHP\IO;

interface IOInterface
{
    /**
     * Is this input means interactive?
     *
     */
    public function isInteractive(): bool;

    /**
     * Is this output verbose?
     *
     */
    public function isVerbose(): bool;

    /**
     * Is the output very verbose?
     *
     */
    public function isVeryVerbose(): bool;

    /**
     * Is the output in debug verbosity?
     *
     */
    public function isDebug(): bool;

    /**
     * Is this output decorated?
     *
     */
    public function isDecorated(): bool;

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     */
    public function write($messages, bool $newline = true);

    /**
     * Writes a message to the error output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     */
    public function writeError($messages, bool $newline = true);
}
