<?php

namespace GrumPHPTest\Console\Command;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Console\Application;
use GrumPHP\Console\Command\RunCommand;
use GrumPHP\FileProvider\DefaultProvider;
use GrumPHP\FileProvider\FileProviderInterface;
use GrumPHP\FileProvider\GitChangedProvider;
use GrumPHP\Runner\ParallelOptions;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

class RunCommandTest extends TestCase
{
    use GrumPHPTestHelperTrait;

    protected function getCommand(array $config = [])
    {
        $app      = $this->resolveApplication($config);
        $original = $app->find(RunCommand::COMMAND_NAME);

        $command = new class($original, $app) extends RunCommand
        {
            /**
             * @var RunCommand
             */
            public $original;
            /**
             * @var Application
             */
            public $app;

            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct(RunCommand $original, Application $app)
            {
                $this->original = $original;
                $this->app      = $app;
            }

            public function resolveTasks(string $str): array
            {
                return $this->original->resolveTasks(... func_get_args());
            }

            public function resolveParallelOptions()
            {
                return $this->original->resolveParallelOptions();
            }

            public function resolveFiles(array $files, string $providerName): FilesCollection
            {
                return $this->original->resolveFiles(... func_get_args());
            }
        };

        return $command;
    }

    /**
     * @test
     * @param string $valueString
     * @param array $expected
     * @dataProvider resolveTasks_dataProvider
     */
    function resolveTasks(string $valueString, array $expected)
    {
        $command = $this->getCommand();

        $actual = $command->resolveTasks($valueString);
        $actual = array_values($actual);

        $this->assertEquals($expected, $actual);
    }

    public function resolveTasks_dataProvider()
    {
        return [
            "default"          => [
                "valueString" => "foo,bar",
                "expected"    => [
                    "foo",
                    "bar",
                ],
            ],
            "trims values"     => [
                "valueString" => "foo , bar",
                "expected"    => [
                    "foo",
                    "bar",
                ],
            ],
            "empty"            => [
                "valueString" => "",
                "expected"    => [],
            ],
            "empty after trim" => [
                "valueString" => " ",
                "expected"    => [],
            ],
        ];
    }

    /**
     * @test
     * @param array $config
     * @param ParallelOptions $expected
     * @dataProvider resolveParallelOptions_dataProvider
     */
    function resolveParallelOptions(array $config, ParallelOptions $expected = null)
    {
        $command = $this->getCommand($config);

        // Note:
        // Relies on config
        $actual = $command->resolveParallelOptions();

        $this->assertEquals($expected, $actual);
    }

    public function resolveParallelOptions_dataProvider()
    {
        return [
            "default"                                  => [
                "config"   => [],
                "expected" => null,
            ],
            "options are resolved with default values" => [
                "config"   => [
                    "parameters" => [
                        "run_in_parallel" => true,
                    ],
                ],
                "expected" => new ParallelOptions(),
            ],
            "options are resolved with given values"   => [
                "config"   => [
                    "parameters" => [
                        "run_in_parallel"        => true,
                        "parallel_process_wait"  => 10,
                        "parallel_process_limit" => 5,
                    ],
                ],
                "expected" => new ParallelOptions(10, 5),
            ],
        ];
    }

    /**
     * @test
     * @param string[] $files
     * @param string $providerName
     * @param string[]|\Exception $expected
     * @dataProvider resolveFiles_dataProvider
     */
    function resolveFiles(array $files, string $providerName, $expected)
    {
        $command = $this->getCommand();
        $this->setNonPublicProperty($command->original, "providers", [
            DefaultProvider::NAME    => new class implements FileProviderInterface
            {
                public function getFiles(): FilesCollection
                {
                    return new FilesCollection([new SplFileInfo("default", "default", "default")]);
                }
            },
            GitChangedProvider::NAME => new class implements FileProviderInterface
            {
                public function getFiles(): FilesCollection
                {
                    return new FilesCollection([new SplFileInfo("git", "git", "git")]);
                }
            },
        ]);

        if ($expected instanceof \Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionMessage($expected->getMessage());
        }
        $filesCollection = $command->resolveFiles($files, $providerName);

        $actual = $filesCollection
            ->map(function (SplFileInfo $fileInfo) {
                return $fileInfo->getRelativePathname();
            })
            ->toArray()
        ;

        $this->assertEquals($expected, $actual);
    }

    public function resolveFiles_dataProvider()
    {
        return [
            "files argument overrides provider"    => [
                "files"        => ["foo"],
                "providerName" => DefaultProvider::NAME,
                "expected"     => ["foo"],
            ],
            "resolves files from default provider" => [
                "files"        => [],
                "providerName" => DefaultProvider::NAME,
                "expected"     => ["default"],
            ],
            "resolves files from git provider"     => [
                "files"        => [],
                "providerName" => GitChangedProvider::NAME,
                "expected"     => ["git"],
            ],
            "fails on unknown provider"            => [
                "files"        => [],
                "providerName" => "unknown",
                "expected"     => new \InvalidArgumentException("Provider 'unknown' does not exist. Valid values: default,changed"),
            ],
        ];
    }
}
