<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Linter\Json\JsonLintError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;

class JsonLintSpec extends AbstractLinterTaskSpec
{
    function let(GrumPHP $grumPHP, JsonLinter $linter)
    {
        $grumPHP->getTaskConfiguration('jsonlint')->willReturn(array());
        $this->beConstructedWith($grumPHP, $linter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\JsonLint');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('jsonlint');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('detect_key_conflicts');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }


    function it_does_not_do_anything_if_there_are_no_files(JsonLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::any())->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection());
        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite(JsonLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setDetectKeyConflicts(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection());

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.json', '.', 'file.json'),
        )));
        $this->run($context);
    }

    function it_throws_exception_if_the_process_fails(JsonLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setDetectKeyConflicts(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection(array(
            new JsonLintError(LintError::TYPE_ERROR, 0, 'error', 'file.json', 1, 1)
        )));

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.json', '.', 'file.json'),
        )));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
