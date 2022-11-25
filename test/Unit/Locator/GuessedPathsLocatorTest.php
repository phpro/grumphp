<?php

declare(strict_types=1);

namespace GrumPHP\Locator {
    function getcwd(): string {
        return $GLOBALS['__current_workspace'] ?? \getcwd();
    }
}


namespace GrumPHPTest\Unit\Locator {

    use GrumPHP\Configuration\GuessedPaths;
    use GrumPHP\Locator\GitRepositoryDirLocator;
    use GrumPHP\Locator\GitWorkingDirLocator;
    use GrumPHP\Locator\GuessedPathsLocator;
    use GrumPHP\Util\ComposerFile;
    use GrumPHP\Util\Filesystem;
    use Prophecy\Argument;
    use Prophecy\PhpUnit\ProphecyTrait;
    use Prophecy\Prophecy\ObjectProphecy;
    use GrumPHPTest\Symfony\FilesystemTestCase;

    class GuessedPathsLocatorTest extends FilesystemTestCase
    {
        use ProphecyTrait;

        /**
         * @var Filesystem
         */
        protected $filesystem;

        /**
         * @var ObjectProphecy|GitRepositoryDirLocator
         */
        private $gitRepositoryDirLocator;

        /**
         * @var ObjectProphecy|GitWorkingDirLocator
         */
        private $gitWorkingDirLocator;

        /**
         * @var GuessedPathsLocator
         */
        private $guesser;

        protected function setUp(): void
        {
            parent::setUp();

            $this->filesystem = new Filesystem();
            $this->gitWorkingDirLocator = $this->prophesize(GitWorkingDirLocator::class);
            $this->gitRepositoryDirLocator = $this->prophesize(GitRepositoryDirLocator::class);

            $this->gitWorkingDirLocator->locate()->willReturn($this->workspace);
            $this->gitRepositoryDirLocator->locate(Argument::any())->will(function (array $arguments) {
                return $arguments[0];
            });

            $this->guesser = new GuessedPathsLocator(
                $this->filesystem,
                $this->gitWorkingDirLocator->reveal(),
                $this->gitRepositoryDirLocator->reveal()
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
            callable $createExpexted,
            ?string $cliConfigFile
        ): void {
            $createSystem($this->filesystem, $this->workspace);
            $guessed = $this->guesser->locate($cliConfigFile ? $this->path($cliConfigFile) : null);
            $this->assertEquals($createExpexted($this->workspace), $guessed);
        }

        public function provideTestCases(): \Generator
        {
            // A dirty configuration callback to make cwd etc work.
            $configure = function (string $workspace) {
                $GLOBALS['__current_workspace'] = $workspace;
                $this->workspace = $workspace;
            };

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);
                },
                function (string $workspace) {
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
                null
            ];

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);
                    $filesystem->dumpFile($this->path('grumphp.yaml.dist'), 'parameters:');
                },
                function (string $workspace) {
                    return new GuessedPaths(
                        $workspace,
                        $this->path('.git'),
                        $workspace,
                        $workspace,
                        $this->path('vendor/bin'),
                        new ComposerFile($this->path('composer.json'), []),
                        $this->path('grumphp.yaml.dist')
                    );
                },
                null
            ];

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);
                    $filesystem->dumpFile($this->path('grumphp.yml'), 'parameters:');
                    $filesystem->dumpFile($this->path('grumphp.yml.dist'), 'parameters:');
                },
                function (string $workspace) {
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
                null
            ];

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);
                    $location = 'vendor/somefile/verynotdiscoverable';
                    $filesystem->mkdir($this->path($location));
                    $filesystem->dumpFile($this->path($location.'/grumphp.yml'), 'parameters:');
                    $filesystem->dumpFile($this->path('composer.json'), '{}');

                },
                function (string $workspace) {
                    return new GuessedPaths(
                        $workspace,
                        $this->path('.git'),
                        $workspace,
                        $workspace,
                        $this->path('vendor/bin'),
                        new ComposerFile($this->path('composer.json'), []),
                        $this->path('vendor/somefile/verynotdiscoverable/grumphp.yml')
                    );
                },
                'vendor/somefile/verynotdiscoverable/grumphp.yml'
            ];


            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);

                    $_SERVER['GRUMPHP_PROJECT_DIR'] = $this->validSlash('project');
                    $_SERVER['GRUMPHP_GIT_WORKING_DIR'] = $this->validSlash('git');
                    $_SERVER['GRUMPHP_GIT_REPOSITORY_DIR'] = $this->validSlash('git/.git/submodule/name');
                    $_SERVER['GRUMPHP_COMPOSER_DIR'] = $this->validSlash('composer');
                    $_SERVER['GRUMPHP_BIN_DIR'] = $this->validSlash('composer/vendor/my-bin');

                    $filesystem->mkdir($this->path('project'));
                    $filesystem->mkdir($this->path('git'));
                    $filesystem->mkdir($this->path('composer'));
                },
                function (string $workspace) {
                    return new GuessedPaths(
                        $this->path('git'),
                        $this->path('git/.git/submodule/name'),
                        $workspace,
                        $this->path('project'),
                        $this->path('composer/vendor/my-bin'),
                        new ComposerFile($this->path('composer/composer.json'), []),
                        $this->path('project/grumphp.yml')
                    );
                },
                null
            ];

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);

                    $_SERVER['GRUMPHP_PROJECT_DIR'] = $this->path('project');
                    $_SERVER['GRUMPHP_GIT_WORKING_DIR'] = $this->path('git');
                    $_SERVER['GRUMPHP_GIT_REPOSITORY_DIR'] = $this->path('git/.git/submodule/name');
                    $_SERVER['GRUMPHP_COMPOSER_DIR'] = $this->path('composer');
                    $_SERVER['GRUMPHP_BIN_DIR'] = $this->path('composer/vendor/my-bin');

                    $filesystem->mkdir($this->path('project'));
                    $filesystem->mkdir($this->path('git'));
                    $filesystem->mkdir($this->path('composer'));
                },
                function (string $workspace) {
                    return new GuessedPaths(
                        $this->path('git'),
                        $this->path('git/.git/submodule/name'),
                        $workspace,
                        $this->path('project'),
                        $this->path('composer/vendor/my-bin'),
                        new ComposerFile($this->path('composer/composer.json'), []),
                        $this->path('project/grumphp.yml')
                    );
                },
                null
            ];

            yield [
                function (Filesystem $filesystem, string $workspace) use ($configure) {
                    $configure($workspace);

                    $_SERVER['GRUMPHP_COMPOSER_DIR'] = $this->path('composer');

                    $filesystem->mkdir($this->path('composer/project'));
                    $filesystem->mkdir($this->path('composer/mybin'));
                    $filesystem->dumpFile($this->path('composer/project/grumphp.yml'), 'parameters:');
                    $filesystem->dumpFile($this->path('composer/composer.json'), json_encode([
                        'config' => [
                            'bin-dir' => 'mybin',
                        ],
                        'extra' => [
                            'grumphp' => [
                                'config-default-path' => 'project/grumphp.yml',
                                'project-path' => 'project'
                            ]
                        ]
                    ]));
                },
                function (string $workspace) {
                    return new GuessedPaths(
                        $workspace,
                        $this->path('.git'),
                        $workspace,
                        $this->path('composer/project'),
                        $this->path('composer/mybin'),
                        new ComposerFile($this->path('composer/composer.json'), [
                            'config' => [
                                'bin-dir' => 'mybin',
                            ],
                            'extra' => [
                                'grumphp' => [
                                    'config-default-path' => 'project/grumphp.yml',
                                    'project-path' => 'project'
                                ]
                            ]
                        ]),
                        $this->path('composer/project/grumphp.yml')
                    );
                },
                null
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
}
