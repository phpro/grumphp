<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Fixer;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Configuration\Model\FixerConfig;
use GrumPHP\Fixer\FixerUpper;
use GrumPHP\Fixer\FixResult;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Style\StyleInterface;

class FixerUpperTest extends TestCase
{

    use ProphecyTrait;

    /**
     * @var ObjectProphecy|IOInterface
     */
    private $IO;

    /**
     * @var ObjectProphecy|StyleInterface
     */
    private $style;

    /**
     * @var FixerConfig
     */
    private $config;

    /**
     * @var FixerUpper
     */
    private $fixerUpper;

    protected function setUp(): void
    {
        $this->IO = $this->prophesize(IOInterface::class);
        $this->style = $this->prophesize(StyleInterface::class);
        $this->IO->style()->willReturn($this->style);
        $this->IO->colorize(Argument::cetera())->will(function (array $arguments): array {
            return $arguments[0];
        });
        $this->IO->isVerbose()->willReturn(false);
        $this->IO->write(Argument::cetera())->willReturn(null);
        $this->style->warning(Argument::cetera())->willReturn(null);

        $this->createFixerUpper(new FixerConfig(true, true));
    }

    private function createFixerUpper(FixerConfig $config): FixerUpper
    {
        $this->config = $config;
        $this->fixerUpper = new FixerUpper($this->IO->reveal(), $config);

        return $this->fixerUpper;
    }

    /** @test */
    public function it_does_not_fix_empty_tasks(): void
    {
        $this->fixerUpper->fix(new TaskResultCollection([]));
        $this->IO->write(Argument::any())->shouldNotBeCalled();
    }

    /** @test */
    public function it_does_not_fix_non_fixable_tasks(): void
    {
        $result = $this->prophesize(TaskResultInterface::class);
        $this->fixerUpper->fix(new TaskResultCollection([$result->reveal()]));
        $this->IO->write(Argument::any())->shouldNotBeCalled();
    }

    /** @test */
    public function it_does_not_fix_when_disabled(): void
    {
        $this->createFixerUpper(new FixerConfig(false, true));

        $result = $this->prophesize(FixableTaskResult::class);
        $this->fixerUpper->fix(new TaskResultCollection([$result->reveal()]));
        $this->IO->write(Argument::any())->shouldNotBeCalled();
    }

    /** @test */
    public function it_does_not_fix_when_canceled_by_user(): void
    {
        $result = $this->prophesize(FixableTaskResult::class);
        $this->style->confirm(Argument::type('string'), true)->willReturn(false);
        $this->fixerUpper->fix(new TaskResultCollection([$result->reveal()]));
        $this->IO->write(Argument::any())->shouldNotBeCalled();
    }

    /** @test */
    public function it_uses_default_configuration_when_user_cannot_answer_fix_question(): void
    {
        $this->createFixerUpper(new FixerConfig(true, false));

        $result = $this->prophesize(FixableTaskResult::class);
        $this->style->confirm(Argument::type('string'), false)->willThrow(new RuntimeException('error'));
        $this->fixerUpper->fix(new TaskResultCollection([$result->reveal()]));
        $this->IO->write(Argument::any())->shouldNotBeCalled();
    }

    /** @test */
    public function it_can_fix_fixable_tasks(): void
    {
        /** @var FixableTaskResult $result1 */
        $result1 = $this->prophesize(FixableTaskResult::class);
        $result1->getTask()->willReturn($this->mockTask('task1'));
        $result1->fix()->shouldBeCalled()->willReturn(FixResult::success('ok'));

        $result2 = $this->prophesize(FixableTaskResult::class);
        $result2->getTask()->willReturn($this->mockTask('task2'));
        $result2->fix()->shouldBeCalled()->willReturn(FixResult::success('ok'));

        $this->style->confirm(Argument::type('string'), true)->willReturn(true);
        $this->fixerUpper->fix(new TaskResultCollection([$result1->reveal(), $result2->reveal()]));
    }

    private function mockTask($name): TaskInterface
    {
        /** @var TaskInterface $task */
        $task = $this->prophesize(TaskInterface::class);
        $task->getConfig()->willReturn(new TaskConfig($name, [], new Metadata([])));

        return $task->reveal();
    }
}
