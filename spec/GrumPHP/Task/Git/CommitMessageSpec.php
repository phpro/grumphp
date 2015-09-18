<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\GitCommitMsgContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommitMessageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP, array(
            'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST']
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Git\CommitMessage');
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement('GrumPHP\Task\TaskInterface');
    }

    function it_should_run_in_git_commit_msg_context(GitCommitMsgContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_the_suite(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('test');

        $this->run($context);
    }

    function it_throws_exception_if_the_process_fails(GitCommitMsgContext $context) {
        $context->getCommitMessage()->willReturn('invalid');
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
