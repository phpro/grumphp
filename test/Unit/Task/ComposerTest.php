<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Composer;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use GrumPHP\Util\Filesystem;
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

    public static function provideConfigurableOptions(): iterable
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
        yield 'exitCode1' => [
            [],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'containsLocalRepo' => [
            [
                'no_local_repository' => true,
            ],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->filesystem->readPath('composer.json')->willReturn(
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
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->filesystem->readPath('composer.json')->willReturn(
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

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
            }
        ];
        yield 'noRepoInfo' => [
            [
                'no_local_repository' => true,
            ],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->filesystem->readPath('composer.json')->willReturn(
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
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->filesystem->readPath('composer.json')->willReturn(
                    json_encode([
                        'repositories' => [
                            ['type' => 'git'],
                        ],
                    ])
                );
            }
        ];
        yield 'packagistDotOrgDisabled' => [
            [
                'no_local_repository' => true,
            ],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->filesystem->readPath('composer.json')->willReturn(
                    json_encode([
                        'repositories' => [
                            ['packagist.org' => false],
                        ],
                    ])
                );
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-no-composer-json' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                './composer.json',
            ]
        ];
        yield 'lock-only' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
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
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
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
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
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
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
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
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
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
            self::mockContext(RunContext::class, ['src/composer.json', 'hello2.php']),
            'composer',
            [
                'validate',
                'src/composer.json',
            ]
        ];
    }
}
