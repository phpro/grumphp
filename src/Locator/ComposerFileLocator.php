<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;

class ComposerFileLocator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Filesystem $filesystem, Paths $paths)
    {
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    public function locate(): ComposerFile
    {
        $guessedPath = $this->filesystem->guessDir(
            [
                $this->paths->getWorkingDir(),
                $this->paths->getGrumPHPConfigDir(),
                $this->paths->getGitDir(),
            ],
            function (string $dir): bool {
                return $this->filesystem->exists([
                    $dir,
                    $this->filesystem->buildPath($dir, 'composer.json')
                ]);
            }
        );

        return $this->locateAtPath($guessedPath);
    }

    public function locateAtPath(string $path): ComposerFile
    {
        if (!$this->filesystem->exists($path)) {
            throw new FileNotFoundException($path);
        }

        $json = $this->filesystem->readFromFileInfo(new \SplFileInfo($path));

        return new ComposerFile($path, json_decode($json, true));
    }
}
