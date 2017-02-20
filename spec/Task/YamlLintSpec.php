<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Linter\Yaml\YamlLintError;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\YamlLint;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YamlLintSpec extends AbstractLinterTaskSpec
{
    public function let(GrumPHP $grumPHP, YamlLinter $linter)
    {
        $grumPHP->getTaskConfiguration('yamllint')->willReturn([]);
        $this->beConstructedWith($grumPHP, $linter);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(YamlLint::class);
    }

    public function it_should_have_a_name()
    {
        $this->getName()->shouldBe('yamllint');
    }

    public function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('object_support');
        $options->getDefinedOptions()->shouldContain('exception_on_invalid_type');
    }

    public function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    public function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    public function it_does_not_do_anything_if_there_are_no_files(YamlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->lint(Argument::any())->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    public function it_runs_the_suite(YamlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setObjectSupport(false)->shouldBeCalled();
        $linter->setExceptionOnInvalidType(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection());

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file.yaml', '.', 'file.yaml'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    public function it_throws_exception_if_the_process_fails(YamlLinter $linter, ContextInterface $context)
    {
        $linter->isInstalled()->willReturn(true);
        $linter->setObjectSupport(false)->shouldBeCalled();
        $linter->setExceptionOnInvalidType(false)->shouldBeCalled();
        $linter->lint(Argument::type('SplFileInfo'))->willReturn(new LintErrorsCollection([
            new YamlLintError(LintError::TYPE_ERROR, 0, 'error', 'file.yaml', 1, 1)
        ]));

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file.yaml', '.', 'file.yaml'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
