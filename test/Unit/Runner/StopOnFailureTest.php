<?php
declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner;

use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\RunContext;
use GrumPHPTest\Helpers\ContextMocks;
use GrumPHPTest\Helpers\TaskMocks;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StopOnFailureTest extends TestCase
{
    use ProphecyTrait;
    use TaskMocks;
    use ContextMocks;

    /** @test */
    public function it_can_stop_cancelation_if_enabled(): void
    {
        $stopOnFailure = $this->createStopOnFailure(true);
        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->stop();

        self::assertTrue($cancellation->isRequested());
    }

    /** @test */
    public function it_can_not_stop_cancelation_if_disabled(): void
    {
        $stopOnFailure = $this->createStopOnFailure(false);

        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->stop();

        self::assertFalse($cancellation->isRequested());
    }

    /** @test */
    public function it_can_decide_for_failing_result(): void
    {
        $stopOnFailure = $this->createStopOnFailure(true);

        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->decideForResult(TaskResult::createFailed(
            $this->mockTask('task', ['blocking' => true]),
            $this->mockContext(),
            ''
        ));

        self::assertTrue($cancellation->isRequested());
    }

    /** @test */
    public function it_can_decide_for_non_blocking_result(): void
    {
        $stopOnFailure = $this->createStopOnFailure(true);

        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->decideForResult(TaskResult::createNonBlockingFailed(
            $this->mockTask('task', ['blocking' => false]),
            $this->mockContext(),
            ''
        ));

        self::assertFalse($cancellation->isRequested());
    }

    /** @test */
    public function it_can_decide_for_passed_result(): void
    {
        $stopOnFailure = $this->createStopOnFailure(true);

        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->decideForResult(TaskResult::createPassed(
            $this->mockTask('task', ['blocking' => false]),
            $this->mockContext(),
            ''
        ));

        self::assertFalse($cancellation->isRequested());
    }

    /** @test */
    public function it_can_decide_for_skipped_result(): void
    {
        $stopOnFailure = $this->createStopOnFailure(true);

        $cancellation = $stopOnFailure->cancellation();
        $stopOnFailure->decideForResult(TaskResult::createPassed(
            $this->mockTask('task', ['blocking' => false]),
            $this->mockContext(),
            ''
        ));

        self::assertFalse($cancellation->isRequested());
    }

    private function createStopOnFailure(bool $enabled): StopOnFailure
    {
        return StopOnFailure::createFromConfig(
            new RunnerConfig(stopOnFailure: $enabled, hideCircumventionTip: true, additionalInfo: null)
        );
    }

}
