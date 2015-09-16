<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Exception\FailureException;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskRunnerHelperSpec extends ObjectBehavior
{
    function let(TaskRunner $taskRunner, HelperSet $helperSet, PathsHelper $pathsHelper)
    {
        $this->beConstructedWith($taskRunner);

        $helperSet->get(PathsHelper::HELPER_NAME)->willreturn($pathsHelper);
        $this->setHelperSet($helperSet);
    }

    function it_should_return_error_code_during_exceptions(OutputInterface $output, TaskRunner $taskRunner, ContextInterface $context)
    {
        $taskRunner->run($context)->willThrow(new FailureException());
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_ERROR);
    }

    function it_should_return_success_code_during_exceptions(OutputInterface $output, TaskRunner $taskRunner, ContextInterface $context)
    {
        $taskRunner->run($context)->shouldBeCalled();
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }
}
