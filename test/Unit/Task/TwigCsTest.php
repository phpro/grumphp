<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TwigCs;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class TwigCsTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new TwigCs(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'path' => '.',
                'severity' => 'warning',
                'display' => 'all',
                'ruleset' => 'FriendsOfTwig\Twigcs\Ruleset\Official',
                'triggered_by' => ['twig'],
                'exclude' => [],
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
            $this->mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twigcs', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twigcs', $this->mockProcess(0));
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
        yield 'no-files-after-triggered-by' => [
            [],
            $this->mockContext(RunContext::class, ['notatwigfile.txt']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'path' => [
            [
                'path' => 'src',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                'src',
            ]
        ];

        yield 'severity' => [
            [
                'severity' => 'error',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=error',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'display' => [
            [
                'display' => 'blocking',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=blocking',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'exclude' => [
            [
                'exclude' => ['src/', 'test/', null, '', false],
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '--exclude=src/',
                '--exclude=test/',
                '.',
            ]
        ];

        yield 'precommi' => [
            [
                'path' => 'src',
            ],
            $this->mockContext(GitPreCommitContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                'hello.twig',
                'hello2.twig',
            ]
        ];
    }
}
