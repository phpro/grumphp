<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpVersion;
use GrumPHP\Task\YamlLint;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @mixin YamlLint
 */
class PhpVersionSpec
{
    function let(GrumPHP $grumPHP, PhpVersionSpec $version)
    {
        $grumPHP->getTaskConfiguration('phpversion')->willReturn([]);
        $this->beConstructedWith([]);
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

    function it_runs_the_suite(PhpVersion $version, ContextInterface $context)
    {
        $result = $this->run($context);
        $result->isPassed()->shouldBe(true);
    }
}
