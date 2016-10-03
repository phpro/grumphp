<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Twig\TwigLinter;
use GrumPHP\Linter\Twig\TwigLintError;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TwigLint;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @mixin TwigLint
 */
class TwigLintSpec extends AbstractLinterTaskSpec
{
    function let(GrumPHP $grumPHP, TwigLinter $linter)
    {
        $grumPHP->getTaskConfiguration('twiglint')->willReturn(array());
        $this->beConstructedWith($grumPHP, $linter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\TwigLint');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('twiglint');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('triggered_by');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files(TwigLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::any())->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(TwigLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection());

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.twig', '.', 'file.twig'),
        )));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(TwigLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection(array(
            new TwigLintError(LintError::TYPE_ERROR, 0, 'error', 'file.twig', 1, 1)
        )));

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.twig', '.', 'file.twig'),
        )));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->isPassed()->shouldBe(false);
    }
}
