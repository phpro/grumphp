<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

use GrumPHP\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractE2ETestCase extends TestCase
{
    /**
     * @var ExecutableFinder
     */
    protected $executableFinder;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $rootDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->executableFinder = new ExecutableFinder();

        $tmpDir = sys_get_temp_dir().$this->useCorrectDirectorySeparator('/grumpytests');
        $this->filesystem->mkdir($tmpDir);

        $this->hash = md5(get_class($this).'::'.$this->getName());
        $this->rootDir = $tmpDir.$this->useCorrectDirectorySeparator('/'.$this->hash);

        $this->removeRootDir();
        $this->filesystem->mkdir($this->rootDir);
    }

    protected function tearDown(): void
    {
        $this->removeRootDir();
    }

    protected function initializeGitInRootDir()
    {
        $this->initializeGit($this->rootDir);
        $this->appendToGitignore($this->rootDir);
    }

    protected function initializeGit(string $gitPath)
    {
        $process = new Process([$this->executableFinder->find('git'), 'init'], $gitPath);
        $this->runCommand('install git', $process);
    }

    protected function initializeGitSubModule(string $gitPath, string $submodulePath): string
    {
        // Change permissions on windows before submodule can be added:
        $this->changeGitPermissions();

        $process = new Process(
            [
                $this->executableFinder->find('git'),
                'submodule',
                'add',
                '-f',
                $this->filesystem->makePathRelative($submodulePath, $gitPath)
            ],
            $gitPath
        );
        $this->runCommand('init git submodule', $process);

        return $this->filesystem->buildPath($gitPath, basename($submodulePath));
    }

    protected function appendToGitignore(string $gitPath, array $paths = ['vendor'])
    {
        $gitignore = $this->filesystem->buildPath($gitPath, '.gitignore');
        $this->filesystem->appendToFile($gitignore, implode(PHP_EOL, $paths));
    }

    protected function initializeComposer(string $path): string
    {
        $process = new Process(
            [
                realpath($this->executableFinder->find('composer', 'composer')),
                'init',
                '--name=grumphp/testsuite'.$this->hash,
                '--type=library',
                '--require-dev=phpro/grumphp:'.$this->detectCurrentGrumphpGitBranchForComposerWithFallback(),
                '--require-dev=phpunit/phpunit:*',
                '--repository='.json_encode([
                    'type' => 'path',
                    'url' => PROJECT_BASE_PATH,
                    'options' => [
                        'symlink' => false,
                    ],
                ]),
                '--no-interaction',
            ],
            $path
        );

        $this->runCommand('initialize composer', $process);

        $composerFile = $path.$this->useCorrectDirectorySeparator('/composer.json');

        $this->mergeComposerConfig($composerFile, [
            'autoload' => [
                'psr-4' => [
                    '' => 'src/',
                ],
            ],
            'config' => [
                'allow-plugins' => [
                    'phpro/grumphp' => true,
                ],
            ],
        ]);

        return $composerFile;
    }

    private function detectCurrentGrumphpGitBranchForComposerWithFallback(): string
    {
        $gitExecutable = $this->executableFinder->find('git');
        $process = new Process([$gitExecutable, 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->run();

        if (!$process->isSuccessful()) {
            return '*';
        }

        // Detached HEAD (for CI)
        $version = trim($process->getOutput());
        if ('HEAD' === $version) {
            // Check if current commit matches a tag:
            $process = new Process([$gitExecutable, 'describe', '--exact-match']);
            $process->run();
            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }

            // Load the sha hash instead
            $process = new Process([$gitExecutable, 'rev-parse', '--verify', 'HEAD']);
            $process->run();
            if (!$process->isSuccessful()) {
                return '*';
            }
            $version = trim($process->getOutput());
        }

        return 'dev-'.$version;
    }

    protected function mergeComposerConfig(string $composerFile, array $config, $recursive = true)
    {
        $this->assertFileExists($composerFile);
        $source = json_decode(file_get_contents($composerFile), true);
        $newSource = $recursive ? array_merge_recursive($source, $config) : array_merge($source, $config);
        $flags = JSON_FORCE_OBJECT+JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES;
        $this->dumpFile($composerFile, json_encode($newSource,  $flags));
    }

    protected function ensureHooksExist(string $gitPath = null, string $containsPattern = '{grumphp}')
    {
        $gitPath = $gitPath ?: $this->rootDir;
        $hooks = ['pre-commit', 'commit-msg'];
        foreach ($hooks as $hook) {
            $hookFile = $gitPath.$this->useCorrectDirectorySeparator('/.git/hooks/'.$hook);
            $this->assertFileExists($hookFile);
            $this->assertMatchesRegularExpression(
                $containsPattern,
                file_get_contents($hookFile),
                $hookFile.' does not contain '.$containsPattern
            );
        }
    }

    protected function initializeGrumphpConfig(string $path, string $fileName = 'grumphp.yml'): string
    {
        $grumphpFile = $this->useCorrectDirectorySeparator($path.'/'.$fileName);

        $this->filesystem->dumpFile(
            $grumphpFile,
            Yaml::dump([
                'grumphp' => [
                    // Don't run E2E tests in parallel.
                    // This causes a deep nesting of parallel running tasks - which is causing some CI issues.
                    'parallel' => [
                        'enabled' => false,
                    ],
                    'tasks' => []
                ]
            ])
        );

        return $grumphpFile;
    }

    protected function mergeGrumphpConfig(string $grumphpFile, array $config)
    {
        $this->assertFileExists($grumphpFile);
        $source = Yaml::parseFile($grumphpFile);
        $newSource = array_merge_recursive($source, $config);
        $this->dumpFile($grumphpFile, Yaml::dump($newSource));
    }

    protected function registerGrumphpDefaultPathInComposer(string $composerFile, string $grumphpFile)
    {
        $configDefaultPath = rtrim($this->filesystem->makePathRelative($grumphpFile, dirname($composerFile)), '\\/');

        $this->mergeComposerConfig($composerFile, [
            'extra' => [
                'grumphp' => [
                    'config-default-path' => $this->useUnixDirectorySeparator($configDefaultPath),
                ],
            ],
        ]);
    }

    protected function registerGrumphpProjectPathInComposer(string $composerFile, string $projectPath): void
    {
        $relativeProjectPath = $this->useUnixDirectorySeparator(
            rtrim($this->filesystem->makePathRelative($projectPath, dirname($composerFile)), '\\/')
        );

        $this->mergeComposerConfig(
            $composerFile,
            [
                'autoload' => [
                    'psr-4' => [
                        '' => $relativeProjectPath.'/src/',
                    ],
                ],
                'extra' => [
                    'grumphp' => [
                        'project-path' => $relativeProjectPath,
                    ],
                ],
            ],
            false
        );
    }

    protected function ensureGrumphpE2eTasksDir(string $projectDir): string
    {
        return $this->mkdir($projectDir.'/src/GrumPHPE2E');
    }

    protected function enableValidatePathsTask(string $grumphpFile, string $projectDir, array $metadata = [])
    {
        $e2eDir = $this->ensureGrumphpE2eTasksDir($projectDir);
        $this->dumpFile(
            $e2eDir.'/ValidatePathsTask.php',
            file_get_contents(TEST_BASE_PATH.'/fixtures/e2e/tasks/ValidatePathsTask.php')
        );

        // Added to test aliases:
        $taskName = array_key_exists('task', $metadata) ? 'dummyTaskName' : 'validatePaths';

        $this->mergeGrumphpConfig($grumphpFile, [
            'grumphp' => [
                'tasks' => [
                    $taskName => [
                        'metadata' => $metadata
                    ],
                ],
            ],
            'services' => [
                'GrumPHPE2E\\ValidatePathsTask' => [
                    'arguments' => [
                        $this->getAvailableFilesInPath($projectDir),
                    ],
                    'tags' => [
                        [
                            'name' => 'grumphp.task',
                            'task' => 'validatePaths'
                        ],
                    ]
                ]
            ],
        ]);
    }

    protected function installComposer(string $path, array $arguments = [])
    {
        $process = new Process(
            array_merge(
                [
                    realpath($this->executableFinder->find('composer', 'composer')),
                    'install',
                    '--optimize-autoloader',
                    '--no-interaction',
                    '-vvv'
                ],
                $arguments),
            $path
        );

        $this->runCommand('install composer', $process);
    }

    protected function commitAll(string $gitPath = null)
    {
        $gitPath = $gitPath ?: $this->rootDir;
        $git = $this->executableFinder->find('git');
        $this->gitAddPath($gitPath);
        $this->runCommand('commit', $commit = new Process([$git, 'commit', '-mtest'], $gitPath));

        $allOutput = $commit->getOutput().$commit->getErrorOutput();
        $this->assertStringContainsString('GrumPHP', $allOutput);
    }

    /**
     * This method triggers a partial commit and uses the diff command in the git hooks:
     * --all: Tell the command to automatically stage files that have been modified and deleted,
     * but new files you have not told Git about are not affected.
     */
    protected function commitModifiedAndDeleted(string $gitPath = null)
    {
        $gitPath = $gitPath ?: $this->rootDir;
        $git = $this->executableFinder->find('git');
        $this->runCommand('commit', $commit = new Process([$git, 'commit', '--all', '-mtest'], $gitPath));

        $allOutput = $commit->getOutput().$commit->getErrorOutput();
        $this->assertStringContainsString('GrumPHP', $allOutput);
    }

    protected function gitAddPath(string $path)
    {
        $git = $this->executableFinder->find('git');
        $this->runCommand('add files to git', new Process([$git, 'add', '-A'], $path));
    }

    protected function runGrumphp(string $projectPath, $vendorPath = './vendor', $environment = [])
    {
        $projectPath = $this->relativeRootPath($projectPath);
        $this->runCommand('grumphp run', (
            new Process(
                [$vendorPath.'/bin/grumphp', 'run', '-vvv'],
                $projectPath
            )
        )->setEnv($environment));
    }

    protected function runGrumphpWithConfig(string $projectPath, string $grumphpFile, $vendorPath = './vendor')
    {
        $projectPath = $this->relativeRootPath($projectPath);
        $this->runCommand('grumphp run with config',
            new Process(
                [$vendorPath.'/bin/grumphp', 'run', '-vvv', '--config='.$grumphpFile],
                $projectPath
            )
        );
    }

    protected function runGrumphpInfo(string $projectPath, $vendorPath = './vendor')
    {
        $projectPath = $this->relativeRootPath($projectPath);
        $this->runCommand('grumphp info',
            new Process(
                [$vendorPath.'/bin/grumphp'],
                $projectPath
            )
        );
    }

    protected function initializeGrumphpGitHooksWithConfig(string $grumphpFile, $vendorPath = './vendor')
    {
        $this->runCommand(
            'grumphp git:init',
            new Process(
                [$vendorPath.'/bin/grumphp', 'git:init', '--config='.$grumphpFile],
                $this->rootDir
            )
        );
    }

    protected function mkdir(string $path): string
    {
        $pathDir = $this->relativeRootPath($path);
        $this->filesystem->mkdir($pathDir);

        return $pathDir;
    }

    protected function dumpFile(string $path, string $contents): string
    {
        $filePath = $this->relativeRootPath($path);
        $this->filesystem->dumpFile($filePath, $contents);

        return $filePath;
    }

    protected function runCommand(string $action, Process $process)
    {
        /*
         * Method is removed in symfony/process:5.0
         * Still required in older symfony versions though (3.4 -> 4.4)
         */
        if (method_exists($process, 'inheritEnvironmentVariables')) {
            $process->inheritEnvironmentVariables(true);
        }
        $process->setTimeout(300);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'Could not '.$action.'! '.$process->getOutput().PHP_EOL.$process->getErrorOutput()
                . PHP_EOL . 'While running '.$process->getCommandLine()
            );
        }
    }

    protected function getAvailableFilesInPath(string $path): array
    {
        $path = $this->relativeRootPath($path);
        $this->gitAddPath($path);

        $process = new Process([$this->executableFinder->find('git'), 'ls-files'], $path);
        $this->runCommand('git ls-files', $process);

        return array_values(array_filter(explode("\n", $process->getOutput())));
    }

    protected function relativeRootPath(string $path): string
    {
        if (strpos($path, $this->rootDir) === 0) {
            return $this->useCorrectDirectorySeparator($path);
        }

        return $this->rootDir.DIRECTORY_SEPARATOR.$this->useCorrectDirectorySeparator(ltrim($path, '/\\'));
    }

    protected function useCorrectDirectorySeparator(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    protected function useUnixDirectorySeparator(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function removeRootDir()
    {
        if (!$this->filesystem->exists($this->rootDir)) {
            return;
        }

        $this->changeGitPermissions();
        $this->filesystem->remove($this->rootDir);
    }

    /**
     * On WIndows / Appveyor : there are some issues while trying to remove the .git folders.
     * They are fixed by changing the permissions of those git dirs.
     */
    protected function changeGitPermissions()
    {
        if (!$this->filesystem->exists($this->rootDir)) {
            return;
        }

        $gitDirs = Finder::create()
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->directories()
            ->in($this->rootDir)
            ->path('.git');

        /** @var \SplFileInfo $gitDir */
        foreach ($gitDirs as $gitDir) {
            $this->filesystem->chmod($gitDir->getPathname(), 0777, 0000, true);
        }
    }

    protected function debugWhatsInDirectory(string $directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        return array_map(
            function(\SplFileInfo $item): string {
                return $item->getPathname() . ' ('.$item->getPerms().')';
            },
            array_values(iterator_to_array($iterator))
        );
    }
}
