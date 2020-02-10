<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\CloverCoverage;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use GrumPHP\Util\Filesystem;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CloverCoverageTest extends AbstractTaskTestCase
{
    /**
     * @var Filesystem|ObjectProphecy
     */
    private $filesystem;

    protected function provideTask(): TaskInterface
    {
        $this->filesystem = $this->prophesize(Filesystem::class);

        return new CloverCoverage(
            $this->filesystem->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [
                'clover_file' => 'coverage.xml',
            ],
            [
                'level' => 100,
                'clover_file' => 'coverage.xml',
            ]
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
        yield 'fileDoesntExist' => [
            [
                'clover_file' => 'coverage.xml',
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(false);
            },
            'Invalid input file provided'
        ];
        yield 'level0' => [
            [
                'clover_file' => 'coverage.xml',
                'level' => 0,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
            },
            'An integer checked percentage must be given as second parameter'
        ];
        yield 'levelNotReached' => [
            [
                'clover_file' => 'coverage.xml',
                'level' => 100,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
            'Code coverage is 60%, which is below the accepted 100%'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'levelReached' => [
            [
                'clover_file' => 'coverage.xml',
                'level' => 50,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'noMetricElements' => [
            [
                'clover_file' => 'coverage.xml',
                'level' => 50,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/0-elements.xml')
                );
            }
        ];
    }
}
