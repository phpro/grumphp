<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpVersion;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;
use GrumPHP\Util\PhpVersion as PhpVersionUtility;

class PhpVersionSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, PhpVersionUtility $phpVersionUtility)
    {
        $grumPHP->getTaskConfiguration('phpversion')->willReturn([]);
        $this->beConstructedWith($grumPHP, $phpVersionUtility);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PhpVersion::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpversion');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_the_suite(ContextInterface $context)
    {
        $result = $this->run($context);
        $result->isPassed()->shouldBeBool();
    }
}
