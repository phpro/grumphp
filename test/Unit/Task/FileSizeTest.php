<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\FileSize;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileSizeTest extends AbstractTaskTestCase
{
    protected static ?Filesystem $filesystem;

    protected function provideTask(): TaskInterface
    {
        return new FileSize();
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'max_size' => '10M',
                'ignore_patterns' => [],
            ]
        ];

        yield 'invalidcase' => [
            [
                'ignore_patterns' => 'thisisnotanarray'
            ],
            null
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            self::mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            self::mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            self::mockContext()
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        $singleFile1 = self::fixture('single-invalid/file1.php', 6);
        $singleFile2 = self::fixture('single-invalid/file2.php', 12);
        yield 'single-invalid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [$singleFile1, $singleFile2]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- '. $singleFile2 .' exceeded the maximum size of 10M.'.PHP_EOL,
        ];

        $invalidFile1 = self::fixture('invalid/file1.php', 12);
        $invalidFile2 = self::fixture('invalid/file2.php', 12);
        yield 'invalid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [$invalidFile1, $invalidFile2]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- '.$invalidFile1.' exceeded the maximum size of 10M.'.PHP_EOL.
            '- '.$invalidFile2.' exceeded the maximum size of 10M.'.PHP_EOL,
        ];

        $customFile1 = self::fixture('invalid-custom/file1.php', 12);
        $customFile2 = self::fixture('invalid-custom/file2.php', 12);
        yield 'invalid-filesizes-custom-size' => [
            [
                'max_size' => '5M'
            ],
            self::mockContext(RunContext::class, [$customFile1, $customFile2]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- '.$customFile1.' exceeded the maximum size of 5M.'.PHP_EOL.
            '- '.$customFile2.' exceeded the maximum size of 5M.'.PHP_EOL,
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'valid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [
                self::fixture('valid/file1.php', 6),
                self::fixture('valid/file2.php', 6),
            ]),
            function () {
            }
        ];
        yield 'dont-validate-ignored-files' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, [
                self::fixture('ignored/test/file.php', 12),
            ]),
            function () {}
        ];
        yield 'dont-validate-symlinks' => [
            [],
            self::mockContext(RunContext::class, [
                self::fixtureSymlink('symlinks/file.php', 12),
            ]),
            function () {}
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {
            },
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::filesystem()->remove(self::fixtureRoot());
    }

    private static function fixture(string $relative, int $sizeMB): string
    {
        $path = self::fixtureRoot() . '/' . $relative;

        self::filesystem()->mkdir(\dirname($path));
        self::createFileWithContent($path, $sizeMB);

        return $path;
    }

    private static function fixtureSymlink(string $relative, int $sizeMB): string
    {
        $linkPath = self::fixtureRoot() . '/' . $relative;
        self::filesystem()->mkdir(\dirname($linkPath));

        $targetPath = \dirname($linkPath) . '/_target_' . \basename($linkPath);
        self::createFileWithContent($targetPath, $sizeMB);

        self::filesystem()->remove($linkPath);
        self::filesystem()->symlink($targetPath, $linkPath);

        return $linkPath;
    }

    private static function createFileWithContent(string $path, int $sizeMB): void
    {
        $fh = \fopen($path, 'w');
        \ftruncate($fh, $sizeMB * 1024 * 1024);
        \fclose($fh);
    }

    private static function fixtureRoot(): string
    {
        return \sys_get_temp_dir() . '/grumphp-filesize-test';
    }

    private static function filesystem(): Filesystem
    {
        self::$filesystem ??= new Filesystem();

        return self::$filesystem;
    }
}
