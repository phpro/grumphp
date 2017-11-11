<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Parser\ParserInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpParser;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhpParserSpec extends AbstractParserTaskSpec
{
    public function let(GrumPHP $grumPHP, ParserInterface $parser)
    {
        $parser->isInstalled()->willReturn(true);
        $grumPHP->getTaskConfiguration('phpparser')->willReturn([]);
        $this->beConstructedWith($grumPHP, $parser);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PhpParser::class);
    }

    public function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpparser');
    }

    public function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('kind');
        $options->getDefinedOptions()->shouldContain('visitors');
    }

    public function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    public function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }
}
