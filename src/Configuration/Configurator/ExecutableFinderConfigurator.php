<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Configurator;

use GrumPHP\Util\Paths;
use Symfony\Component\Process\ExecutableFinder;

class ExecutableFinderConfigurator
{
    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
    }

    public function configure(ExecutableFinder $executableFinder): ExecutableFinder
    {
        $this->prefixBinDir();

        return $executableFinder;
    }

    /**
     * Make sure the bin dir is prepended to your current PATH env var.
     * This way, it first tries to detect dependencies in the folder they will most likely be.
     *
     * Code copied from composer:
     * @see https://github.com/composer/composer/blob/1.1/src/Composer/EventDispatcher/EventDispatcher.php#L147-L160
     */
    private function prefixBinDir(): void
    {
        $pathStr = 'PATH';
        if (!isset($_SERVER[$pathStr]) && isset($_SERVER['Path'])) {
            $pathStr = 'Path';
        }

        if (!is_dir($binDir = $this->paths->getBinDir())) {
            return;
        }

        // add the bin dir to the PATH to make local binaries of deps usable in scripts
        $hasBindDirInPath = preg_match(
            '{(^|'.PATH_SEPARATOR.')'.preg_quote($binDir).'($|'.PATH_SEPARATOR.')}',
            $_SERVER[$pathStr]
        );

        if (!$hasBindDirInPath && isset($_SERVER[$pathStr])) {
            $_SERVER[$pathStr] = $binDir.PATH_SEPARATOR.getenv($pathStr);
            putenv($pathStr.'='.$_SERVER[$pathStr]);
        }
    }
}
