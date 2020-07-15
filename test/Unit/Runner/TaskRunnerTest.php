<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Runner\Middleware\RunnerMiddlewareInterface;
use GrumPHP\Runner\MiddlewareStack;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TaskRunnerTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_can_run_the_specified_tasks(): void
    {
        $expectedResult = new TaskResultCollection();
        $tasks = new TasksCollection();
        $stack = new MiddlewareStack(
            $this->createMiddleware(static function () use ($expectedResult): TaskResultCollection {
                return $expectedResult;
            })
        );
        $context = new TaskRunnerContext(
            new RunContext(new FilesCollection())
        );

        $runner = new TaskRunner($tasks, $stack);
        $result = $runner->run($context);

        self::assertSame($expectedResult, $result);
    }

    private function createMiddleware(callable $run): RunnerMiddlewareInterface
    {
        /** @var ObjectProphecy|RunnerMiddlewareInterface $middleware */
        $middleware = $this->prophesize(RunnerMiddlewareInterface::class);
        $middleware->handle(Argument::cetera())->will(function ($arguments) use ($run): TaskResultCollection {
            return $run(...$arguments);
        });

        return $middleware->reveal();
    }
}
