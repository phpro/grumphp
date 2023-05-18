<?php
declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Parallel;

use Amp\NullCancellation;
use Amp\Sync\Channel;
use GrumPHP\Runner\Parallel\SerializedClosureTask;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class SerializedClosureTaskTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_can_run_serialized_closure(): void
    {
        $task = SerializedClosureTask::fromClosure(fn () => 'hello world');
        $actual = $task->run(
            $this->prophesize(Channel::class)->reveal(),
            new NullCancellation()
        );

        self::assertSame('hello world', $actual);
    }
}
