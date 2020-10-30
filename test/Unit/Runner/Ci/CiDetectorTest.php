<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Ci;

use GrumPHP\Runner\Ci\CiDetector;
use OndraM\CiDetector\CiDetector as RealCiDetector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class CiDetectorTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_can_detect_it_runs_in_well_known_ci_environments(): void
    {
        /** @var ObjectProphecy & RealCiDetector $ciDetector */
        $ciDetector = $this->prophesize(RealCiDetector::class);
        $detector = new CiDetector($ciDetector->reveal());

        $ciDetector->isCiDetected()->willReturn(true);

        self::assertTrue($detector->isCiDetected());
        self::assertTrue($detector->isCiDetected());
        self::assertTrue($detector->isCiDetected());

        $ciDetector->isCiDetected()->shouldHaveBeenCalledOnce();
    }
}
