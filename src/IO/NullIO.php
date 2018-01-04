<?php declare(strict_types=1);

namespace GrumPHP\IO;

class NullIO implements IOInterface
{
    /**
     * {@inheritDoc}
     */
    public function isInteractive(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = true)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function writeError($messages, $newline = true)
    {
    }
}
