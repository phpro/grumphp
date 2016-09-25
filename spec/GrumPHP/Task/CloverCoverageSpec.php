<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpunit;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @mixin Phpunit
 */
class CloverCoverageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, Filesystem $filesystem)
    {
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn(array());
        $grumPHP->getFilesystem()->willReturn($filesystem);
        $this->beConstructedWith($grumPHP);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\CloverCoverage');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('clover_coverage');
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

    function it_runs_the_suite_but_fails_when_file_doesnt_exists(GrumPHP $grumPHP, GitCommitMsgContext $context, Filesystem $filesystem)
    {
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn(array(
            'clover_file' => 'foo.bar',
        ));
        $filesystem->exists('foo.bar')->willReturn(false);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->getResultCode()->shouldBe(TaskResult::FAILED);
    }

    function it_runs_the_suite(GrumPHP $grumPHP, GitCommitMsgContext $context, Filesystem $filesystem)
    {
        $filename = dirname(dirname(dirname(__DIR__))) . '/test/fixtures/clover_coverage/10.xml';
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn(array(
            'clover_file' => $filename,
            'level' => 50,
        ));
        $filesystem->exists($filename)->willReturn(true);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_runs_the_suite_but_not_reaching_coverage(GrumPHP $grumPHP, GitCommitMsgContext $context, Filesystem $filesystem)
    {
        $filename = dirname(dirname(dirname(__DIR__))) . '/test/fixtures/clover_coverage/10.xml';
        $grumPHP->getTaskConfiguration('clover_coverage')->willReturn(array(
            'clover_file' => $filename,
            'level' => 100,
        ));
        $filesystem->exists($filename)->willReturn(true);
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->getResultCode()->shouldBe(TaskResult::FAILED);
        $result->getMessage()->shouldBe('Code coverage is 60%, which is below the accepted 100%' . PHP_EOL);
    }
}
