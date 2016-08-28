<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Parser\ParserInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

class PhpparserSpec extends AbstractParserTaskSpec
{

    function let(GrumPHP $grumPHP, ParserInterface $parser)
    {
        $grumPHP->getTaskConfiguration('php_parser')->willReturn(array());
        $this->beConstructedWith($grumPHP, $parser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Phpparser');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('php_parser');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('visitors_options');
        $options->getDefinedOptions()->shouldContain('visitors');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }
}
