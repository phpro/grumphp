<?php

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

class ExternalCommand
{
    /**
     * @var string
     */
    protected $binDir;

    /**
     * @var ExecutableFinder
     */
    protected $executableFinder;

    /**
     * @param string $binDir
     * @param ExecutableFinder $executableFinder
     */
    public function __construct($binDir, ExecutableFinder $executableFinder)
    {
        $this->binDir = rtrim($binDir, '/\\');
        $this->executableFinder = $executableFinder;
    }

    /**
     * @param string $command
     * @param boolean $forceUnix This parameter makes it possible to force unix style commands
     *                           on a windows environment.
     *                           This can be useful in git hooks.
     *
     * @return string
     *
     * @throws RuntimeException if the command can not be found
     */
    public function locate($command, $forceUnix = false)
    {
        // Search executable:
        $executable = $this->executableFinder->find($command, null, [$this->binDir]);
        if (!$executable) {
            throw new RuntimeException(
                sprintf('The executable for "%s" could not be found.', $command)
            );
        }

        // Make sure to add unix-style directory separators if unix-mode is enforced
        if ($forceUnix) {
            $parts = pathinfo($executable);
            $executable = $parts['dirname'] . '/' . $parts['filename'];
        }

        return $executable;
    }
}
