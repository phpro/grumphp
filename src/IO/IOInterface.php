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
     * @return bool|null
     */
    public function isVeryVerbose();

    /**
     * Is the output in debug verbosity?
     *
     */
    public function isDebug(): bool;

    /**
     * Is this output decorated?
     *
     * @return bool|null
     */
    public function isDecorated();

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline or not
     */
    public function write($messages, $newline = true);

    /**
     * Writes a message to the error output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline or not
     */
    public function writeError($messages, $newline = true);
}
