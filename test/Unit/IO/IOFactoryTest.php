<?php

namespace GrumPHPTest\Unit\IO;

use GrumPHP\IO\ConsoleIO;
use GrumPHP\IO\GitHubActionsIO;
use GrumPHP\IO\IOFactory;
use OndraM\CiDetector\Ci\GitHubActions;
use OndraM\CiDetector\CiDetector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IOFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_can_detect_it_is_not_in_a_ci_environment()
    {
        /** @var ObjectProphecy & CiDetector $ciDetector */
        $ciDetector = $this->prophesize(CiDetector::class);
        /** @var ObjectProphecy & InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        /** @var ObjectProphecy & OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);

        $factory = new IOFactory($ciDetector->reveal());

        $ciDetector->isCiDetected()->shouldBeCalled()->willReturn(false);
        $ciDetector->detect()->shouldNotBeCalled();

        $result = $factory->create($input->reveal(), $output->reveal());

        // Exactly this class, not a child
        self::assertSame(ConsoleIO::class, get_class($result));
    }

    /**
     * @test
     */
    public function it_can_detect_it_is_in_a_ci_environment()
    {
        /** @var ObjectProphecy & CiDetector $ciDetector */
        $ciDetector = $this->prophesize(CiDetector::class);
        /** @var ObjectProphecy & GitHubActions $githubActions */
        $githubActions = $this->prophesize(GitHubActions::class);
        /** @var ObjectProphecy & InputInterface $input */
        $input = $this->prophesize(InputInterface::class);
        /** @var ObjectProphecy & OutputInterface $output */
        $output = $this->prophesize(OutputInterface::class);

        $factory = new IOFactory($ciDetector->reveal());

        $ciDetector->isCiDetected()->shouldBeCalled()->willReturn(true);
        $ciDetector->detect()->shouldBeCalled()->willReturn($githubActions->reveal());

        $result = $factory->create($input->reveal(), $output->reveal());

        // Exactly this class, not a child
        self::assertSame(GitHubActionsIO::class, get_class($result));
    }
}
