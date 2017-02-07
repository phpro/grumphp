<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Xml\XmlLinter;
use GrumPHP\Linter\Xml\XmlLintError;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\XmlLint;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XmlLintSpec extends AbstractLinterTaskSpec
{
    function let(GrumPHP $grumPHP, XmlLinter $linter)
    {
        $grumPHP->getTaskConfiguration('xmllint')->willReturn([]);
        $this->beConstructedWith($grumPHP, $linter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(XmlLint::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('xmllint');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('load_from_net');
        $options->getDefinedOptions()->shouldContain('x_include');
        $options->getDefinedOptions()->shouldContain('dtd_validation');
        $options->getDefinedOptions()->shouldContain('scheme_validation');
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

    function it_does_not_do_anything_if_there_are_no_files(XmlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::any())->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(XmlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setLoadFromNet(false)->shouldBeCalled();
        $linter->setXInclude(false)->shouldBeCalled();
        $linter->setDtdValidation(false)->shouldBeCalled();
        $linter->setSchemeValidation(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection());

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file.xml', '.', 'file.xml'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(XmlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setLoadFromNet(false)->shouldBeCalled();
        $linter->setXInclude(false)->shouldBeCalled();
        $linter->setDtdValidation(false)->shouldBeCalled();
        $linter->setSchemeValidation(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection([
            new XmlLintError(LintError::TYPE_ERROR, 0, 'error', 'file.xml', 1, 1)
        ]));

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file.xml', '.', 'file.xml'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
