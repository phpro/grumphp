<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\PhpVersion;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use GrumPHP\Util\PhpVersion as PhpVersionUtility;
use Prophecy\Prophecy\ObjectProphecy;

class PhpVersionTest extends AbstractTaskTestCase
{
    /**
     * @var PhpVersionUtility|ObjectProphecy
     */
    private $versionUtility;

    protected function provideTask(): TaskInterface
    {
        $this->versionUtility = $this->prophesize(PhpVersionUtility::class);

        return new PhpVersion($this->versionUtility->reveal());
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'project' => null,
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
        yield 'current-version-not-supported' => [
            [
                'project' => '7.4'
            ],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(false);
            },
            'PHP version '.PHP_VERSION.' is unsupported'
        ];
        yield 'project-version-bigger' => [
            [
                'project' => '99.99.1'
            ],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(true);
                $this->versionUtility->isSupportedProjectVersion(PHP_VERSION, $options['project'])->willReturn(false);
            },
            'This project requires PHP version 99.99.1, you have '.PHP_VERSION
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'project-version-lower' => [
            [
                'project' => '4.0'
            ],
            $this->mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(true);
                $this->versionUtility->isSupportedProjectVersion(PHP_VERSION, $options['project'])->willReturn(true);
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-project' => [
            [],
            $this->mockContext(RunContext::class),
            function () {}
        ];
    }
}
