<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpunit;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin Phpunit
 */
class CloverCoverageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $grumPHP->getTaskConfiguration('phpunit')->willReturn(array());
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\CloverCoverage');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('clover_coverage');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_not_run_in_commit_message_context(GitCommitMsgContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(false);
    }
}
