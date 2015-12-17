<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Xml\XmlLinter;
use GrumPHP\Linter\Xml\XmlLintError;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class XmlLintSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, XmlLinter $linter)
    {
        $grumPHP->getTaskConfiguration('xmllint')->willReturn(array());
        $this->beConstructedWith($grumPHP, $linter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\XmlLint');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('xmllint');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('load_from_net');
        $options->getDefinedOptions()->shouldContain('x_include');
        $options->getDefinedOptions()->shouldContain('dtd_validation');
        $options->getDefinedOptions()->shouldContain('scheme_validation');
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
        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite(XmlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setLoadFromNet(false)->shouldBeCalled();
        $linter->setXInclude(false)->shouldBeCalled();
        $linter->setDtdValidation(false)->shouldBeCalled();
        $linter->setSchemeValidation(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection());

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.xml', '.', 'file.xml'),
        )));
        $this->run($context);
    }

    function it_throws_exception_if_the_process_fails(XmlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setLoadFromNet(false)->shouldBeCalled();
        $linter->setXInclude(false)->shouldBeCalled();
        $linter->setDtdValidation(false)->shouldBeCalled();
        $linter->setSchemeValidation(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection(array(
            new XmlLintError(LintError::TYPE_ERROR, 0, 'error', 'file.xml', 1, 1)
        )));

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file.xml', '.', 'file.xml'),
        )));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
