<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskRunnerHelperSpec extends ObjectBehavior
{
    function let(TaskRunner $taskRunner, EventDispatcherInterface $eventDispatcher, HelperSet $helperSet, PathsHelper $pathsHelper)
    {
        $this->beConstructedWith($taskRunner, $eventDispatcher);

        $helperSet->get(PathsHelper::HELPER_NAME)->willreturn($pathsHelper);
        $this->setHelperSet($helperSet);
    }

    function it_should_return_error_code_with_a_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        ContextInterface $context,
        TaskResult $passedTaskResult,
        TaskResult $failedTaskResult
    )
    {
        $passedTaskResult->isPassed()->willReturn(true);
        $failedTaskResult->isPassed()->willReturn(false);
        $failedTaskResult->isBlocking()->willReturn(true);
        $failedTaskResult->getMessage()->willReturn('failed task message');
        $taskResults = new TaskResultCollection();
        $taskResults->add($failedTaskResult->getWrappedObject());
        $taskRunner->run($context)->willReturn($taskResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_ERROR);
    }

    function it_should_return_success_code_with_no_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        ContextInterface $context,
        TaskResult $succeedTaskResult
    )
    {
        $succeedTaskResult->isPassed()->willReturn(true);
        $taskResults = new TaskResultCollection();
        $taskResults->add($succeedTaskResult->getWrappedObject());
        $taskRunner->run($context)->willReturn($taskResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_return_success_code_during_a_failed_of_a_nonblocking_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        ContextInterface $context,
        TaskResult $failedTaskResult
    )
    {
        $failedTaskResult->isPassed()->willReturn(false);
        $failedTaskResult->isBlocking()->willReturn(false);
        $failedTaskResult->getMessage()->willReturn('failed task message');
        $testResults = new TaskResultCollection();
        $testResults->add($failedTaskResult->getWrappedObject());
        $taskRunner->run($context)->willReturn($testResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_add_a_progress_listener_during_run(
        OutputInterface $output,
        TaskRunner $taskRunner,
        ContextInterface $context,
        EventDispatcherInterface $eventDispatcher
    ) {
        $taskRunner->run($context)->willReturn(new TaskResultCollection());
        $eventDispatcher->addSubscriber(Argument::type('GrumPHP\Event\Subscriber\ProgressSubscriber'))->shouldBeCalled();
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }
}
