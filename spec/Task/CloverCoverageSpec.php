<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\CloverCoverage;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CloverCoverageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn([]);
        $this->beConstructedWith($grumPHP, new Filesystem());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CloverCoverage::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('clover_coverage');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('level');
        $options->getDefinedOptions()->shouldContain('clover_file');
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

    function it_runs_the_suite_but_fails_when_file_doesnt_exists(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn([
            'clover_file' => 'foo.bar',
        ]);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::FAILED);
    }

    function it_runs_the_suite(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $filename = dirname(dirname(__DIR__)) . '/test/fixtures/clover_coverage/60-percent-coverage.xml';
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn([
            'clover_file' => $filename,
            'level' => 50,
        ]);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_runs_the_suite_but_not_reaching_coverage(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $filename = dirname(dirname(__DIR__)) . '/test/fixtures/clover_coverage/60-percent-coverage.xml';
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn([
            'clover_file' => $filename,
            'level' => 100,
        ]);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::FAILED);
        $result->getMessage()->shouldBe('Code coverage is 60%, which is below the accepted 100%' . PHP_EOL);
    }
}
