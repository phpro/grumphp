<?php

namespace GrumPHP\IO;

class NullIO implements IOInterface
{
    /**
     * {@inheritDoc}
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
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
