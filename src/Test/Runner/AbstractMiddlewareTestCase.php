<?php

declare(strict_types=1);

namespace GrumPHP\Test\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Style\StyleInterface;

class AbstractMiddlewareTestCase extends TestCase
{
    protected function createRunnerContext(): TaskRunnerContext
    {
        return new TaskRunnerContext(
            new RunContext(new FilesCollection())
        );
    }

    protected function createNextShouldNotBeCalledCallback(): callable
    {
        return static function () {
            throw new \RuntimeException('Expected next not to be called!');
        };
    }

    protected function mockIO(): IOInterface
    {
        /** @var ObjectProphecy|IOInterface $IO */
        $IO = $this->prophesize(IOInterface::class);
        $IO->isVerbose()->willReturn(false);
        $IO->style()->willReturn($this->prophesize(StyleInterface::class)->reveal());

        return $IO->reveal();
    }

    protected function mockTask(string $name, array $meta = []): TaskInterface
    {
        /** @var ObjectProphecy|TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig($name, [], new Metadata($meta)));

        return $task->reveal();
    }
}
