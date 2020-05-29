<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Composer;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use GrumPHP\Util\Filesystem;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ComposerTest extends AbstractExternalTaskTestCase
{
    /**
     * @var Filesystem|ObjectProphecy
     */
    private $filesystem;

    protected function provideTask(): TaskInterface
    {
        $this->filesystem = $this->prophesize(Filesystem::class);

        return new Composer(
            $this->processBuilder->reveal(),
            $this->formatter->reveal(),
            $this->filesystem->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'file' => './composer.json',
                'no_check_all' => false,
                'no_check_lock' => false,
                'no_check_publish' => false,
                'no_local_repository' => false,
                'with_dependencies' => false,
                'strict' => false,
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
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'containsLocalRepo' => [
            [
                'no_local_repository' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'composer.json'))->willReturn(
                    json_encode([
                        'repositories' => [
                            ['type' => 'path'],
                        ],
                    ])
                );
            },
            'You have at least one local repository declared.'
        ];
        yield 'containsLocalAndRemoteRepo' => [
            [
                'no_local_repository' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'composer.json'))->willReturn(
                    json_encode([
                        'repositories' => [
                            ['type' => 'path'],
                            ['type' => 'git'],
                        ],
                    ])
                );
            },
            'You have at least one local repository declared.'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
            }
        ];
        yield 'noRepoInfo' => [
            [
                'no_local_repository' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'composer.json'))->willReturn(
                    json_encode([
                        'name' => 'my/package',
                    ])
                );
            }
        ];
        yield 'noLocalRepo' => [
            [
                'no_local_repository' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess(0));
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'composer.json'))->willReturn(
                    json_encode([
                        'repositories' => [
                            ['type' => 'git'],
                        ],
                    ])
                );
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-no-composer-json' => [
            [],
            $this->mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                './composer.json',
            ]
        ];
        yield 'no-check-all' => [
            [
                'no_check_all' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                '--no-check-all',
                './composer.json',
            ]
        ];
        yield 'no-check-lock' => [
            [
                'no_check_lock' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                '--no-check-lock',
                './composer.json',
            ]
        ];
        yield 'with-dependencies' => [
            [
                'with_dependencies' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                '--with-dependencies',
                './composer.json',
            ]
        ];
        yield 'strict' => [
            [
                'strict' => true,
            ],
            $this->mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                '--strict',
                './composer.json',
            ]
        ];
        yield 'file' => [
            [
                'file' => 'src/composer.json',
            ],
            $this->mockContext(RunContext::class, ['src/composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                'src/composer.json',
            ]
        ];
    }
}
