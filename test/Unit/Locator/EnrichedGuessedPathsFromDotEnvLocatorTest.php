<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Locator;

use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Locator\EnrichedGuessedPathsFromDotEnvLocator;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;
use GrumPHPTest\Symfony\FilesystemTestCase;

class EnrichedGuessedPathsFromDotEnvLocatorTest extends FilesystemTestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var EnrichedGuessedPathsFromDotEnvLocator
     */
    private $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->guesser = new EnrichedGuessedPathsFromDotEnvLocator(
            $this->filesystem
        );

        // Reset grumphp server vars during runs...
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'GRUMPHP_')) {
                unset($_SERVER[$key]);
            }
        }
    }

    /**
     * @dataProvider provideTestCases
     * @test
     */
    public function it_can_guess_paths(
        callable $createSystem,
        callable $input,
        callable $expectedOutput
    ): void {
        $createSystem($this->filesystem, $this->workspace);
        $previous = $input($this->workspace);
        $guessed = $this->guesser->locate($previous);

        self::assertNotSame($previous, $guessed);
        self::assertEquals($expectedOutput($this->workspace), $guessed);
    }

    public function provideTestCases(): \Generator
    {
        // A dirty configuration callback to make cwd etc work.
        $configure = function (string $workspace) {
            $this->workspace = $workspace;
        };

        yield 'keep-paths' => [
            function (Filesystem $filesystem, string $workspace) use ($configure) {
                $configure($workspace);
            },
            $input = function (string $workspace) {
                return new GuessedPaths(
                    $workspace,
                    $this->path('.git'),
                    $workspace,
                    $workspace,
                    $this->path('vendor/bin'),
                    new ComposerFile($this->path('composer.json'), []),
                    $this->path('grumphp.yml')
                );
            },
            $input
        ];


        yield 'overwritten-config' => [
            function (Filesystem $filesystem, string $workspace) use ($configure) {
                $configure($workspace);

                $_SERVER['GRUMPHP_PROJECT_DIR'] = $this->validSlash('project');
                $_SERVER['GRUMPHP_GIT_WORKING_DIR'] = $this->validSlash('git');
                $_SERVER['GRUMPHP_GIT_REPOSITORY_DIR'] = $this->validSlash('git/.git/submodule/name');
                $_SERVER['GRUMPHP_BIN_DIR'] = $this->validSlash('composer/vendor/my-bin');

                $filesystem->mkdir($this->path('project'));
                $filesystem->mkdir($this->path('git'));
                $filesystem->mkdir($this->path('composer'));
            },
            $input,
            function (string $workspace) {
                return new GuessedPaths(
                    $this->path('git'),
                    $this->path('git/.git/submodule/name'),
                    $workspace,
                    $this->path('project'),
                    $this->path('composer/vendor/my-bin'),
                    new ComposerFile($this->path('composer.json'), []),
                    $this->path('grumphp.yml')
                );
            }
        ];
    }

    private function validSlash(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function path(string $path): string
    {
        return $this->workspace.DIRECTORY_SEPARATOR.$this->validSlash($path);
    }
}
