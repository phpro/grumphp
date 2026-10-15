<?php

declare(strict_types=1);

namespace GrumPHPTest\Helpers;

use Gitonomy\Git\Repository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * A git repository on disk: since gitlib 2, its classes are final, and can't be doubled.
 */
final class TemporaryGitRepository
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var Repository
     */
    private $repository;

    private function __construct(string $dir)
    {
        $this->dir = $dir;
        $this->git('init', '--quiet');
        $this->repository = new Repository($dir);
    }

    public static function create(): self
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('grumphp-git-', true);
        (new Filesystem())->mkdir($dir);

        return new self($dir);
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function dumpFile(string $path, string $content = ''): void
    {
        (new Filesystem())->dumpFile($this->dir.DIRECTORY_SEPARATOR.$path, $content);
    }

    public function git(string ...$arguments): void
    {
        (new Process(
            [
                'git',
                '-c', 'commit.gpgSign=false',
                '-c', 'maintenance.auto=false',
                '-c', 'user.email=grumphp@example.com',
                '-c', 'user.name=GrumPHP',
                ...$arguments,
            ],
            $this->dir
        ))->mustRun();
    }

    public function remove(): void
    {
        $filesystem = new Filesystem();
        // git writes its objects read-only, which Windows refuses to remove.
        $filesystem->chmod($this->dir, 0777, 0000, true);
        $filesystem->remove($this->dir);
    }
}
