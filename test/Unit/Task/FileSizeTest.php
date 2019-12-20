<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\FileSize;
use GrumPHP\Task\TaskInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;

class FileSizeTest extends AbstractTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new FileSize();
    }

    public function provideConfigurableOptions(): iterable
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

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            $this->mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            $this->mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            $this->mockContext()
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        /** @var ContextInterface|ObjectProphecy $context */
        $context = $this->prophesize(RunContext::class);
        $files = new FilesCollection(array_map(
            static function (string $file): SplFileInfo {
                return new SplFileInfo($file, $file, $file);
            },
            ['file1.php', 'file2.php']
        ));

        /** @var FilesCollection $filesCollection */
        $filesCollection = $this->prophesize(FilesCollection::class);
        $filesCollection->size('>10M')->willReturn($files);
        $context->getFiles()->willReturn($files);

        yield 'exitCode1' => [
            [],
            $context->reveal(),
            function () {
            },
            'Large files detected:'.PHP_EOL.
            '- file1.php exceeded the maximum size of 10M.'.PHP_EOL.
            '- file2.php exceeded the maximum size of 10M.'.PHP_EOL,
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        /** @var ContextInterface|ObjectProphecy $context */
        $context = $this->prophesize(RunContext::class);
        /** @var FilesCollection $filteredFiles */
        $filteredFiles = $this->prophesize(FilesCollection::class);

        /** @var FilesCollection $filesCollection */
        $filesCollection = $this->prophesize(FilesCollection::class);
        $filesCollection->ignoreSymlinks()->willReturn($filesCollection);
        $filesCollection->notPaths(['src/'])->willReturn($filesCollection);
        $filesCollection->count()->willReturn(1);
        $filesCollection->size('>test')->willReturn($filteredFiles);

        $context->getFiles()->willReturn($filesCollection);

        yield 'exitCode0' => [
            ['ignore_patterns' => ['src/'], 'max_size' => 'test'],
            $context->reveal(),
            function () {
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        /** @var ContextInterface|ObjectProphecy $context */
        $context = $this->prophesize(RunContext::class);
        $filteredFiles = new FilesCollection();

        /** @var FilesCollection $filesCollection */
        $filesCollection = $this->prophesize(FilesCollection::class);
        $filesCollection->count()->willReturn(0);

        $context->getFiles()->willReturn($filesCollection);

        yield 'no-files' => [
            [],
            $context->reveal(),
            function () {
            },
        ];
    }
}
