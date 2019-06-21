<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;

class GitHooksDirLocator
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

    public function locate(): string
    {
        $gitConfigDir = $this->filesystem->buildPath($this->paths->getGitDir(), '.git');
        if ($this->filesystem->isFile($gitConfigDir)) {
            return $this->locateGitHooksFromSubmodule($gitConfigDir);
        }

        return $this->filesystem->buildPath($gitConfigDir, 'hooks');
    }

    private function locateGitHooksFromSubmodule(string $gitSubmoduleFile): string
    {
        $content = $this->filesystem->readPath($gitSubmoduleFile);
        if (!preg_match('/gitdir:\s+(\S+)/', $content, $matches)) {
            return $gitSubmoduleFile;
        }

        $gitConfigDir = $matches[1];

        return $this->filesystem->buildPath(
            $this->filesystem->makePathRelative($gitConfigDir, $gitSubmoduleFile),
            'hooks'
        );
    }
}
