<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class TaskRunnerHelperSpec
 */
class TaskRunnerHelperSpec extends ObjectBehavior
{
    function let(TaskRunner $taskRunner, EventDispatcherInterface $eventDispatcher, HelperSet $helperSet, PathsHelper $pathsHelper)
    {
        $this->beConstructedWith($taskRunner, $eventDispatcher);

        $helperSet->get(PathsHelper::HELPER_NAME)->willreturn($pathsHelper);
        $this->setHelperSet($helperSet);
    }

    function it_is_a_console_helper()
    {
        $this->shouldHaveType(Helper::class);
    }

    function it_should_return_error_code_with_a_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        ContextInterface $context
    )
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $passedTaskResult = TaskResult::createPassed($aTask, $aContext);
        $failedTaskResult = TaskResult::createFailed($aTask, $aContext, 'failed task message');
        $taskResults = new TaskResultCollection();
        $taskResults->add($passedTaskResult);
        $taskResults->add($failedTaskResult);
        $taskRunner->run($context)->willReturn($taskResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_ERROR);
    }

    function it_should_return_success_code_with_no_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        ContextInterface $context
    )
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $passedTaskResult = TaskResult::createPassed($aTask, $aContext);
        $taskResults = new TaskResultCollection();
        $taskResults->add($passedTaskResult);
        $taskRunner->run($context)->willReturn($taskResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_return_success_code_during_a_failed_of_a_nonblocking_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        ContextInterface $context
    )
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $nonBlockingFailedTaskResult = TaskResult::createNonBlockingFailed($aTask, $aContext, 'failed task message');
        $testResults = new TaskResultCollection();
        $testResults->add($nonBlockingFailedTaskResult);
        $taskRunner->run($context)->willReturn($testResults);
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_display_all_errors_of_failed_tasks(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        ContextInterface $context
    )
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $failedTaskResult = TaskResult::createFailed($aTask, $aContext, 'failed task message');
        $anotherFailedTaskResult = TaskResult::createFailed($aTask, $aContext, 'another failed task message');
        $taskResults = new TaskResultCollection();
        $taskResults->add($failedTaskResult);
        $taskResults->add($anotherFailedTaskResult);
        $taskRunner->run($context)->willReturn($taskResults);

        $output->isDecorated()->willReturn(false);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(Argument::containingString('failed task message'))->shouldBeCalled();
        $output->writeln(Argument::containingString('another failed task message'))->shouldBeCalled();
        $output->writeln(Argument::any())->shouldBeCalled();

        $this->run($output, $context);
    }

    function it_should_display_warning_of_non_blocking_failed_tasks(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        ContextInterface $context
    )
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $nonBlockingFailedTaskResult = TaskResult::createNonBlockingFailed($aTask, $aContext, 'non blocking task message');
        $taskResults = new TaskResultCollection();
        $taskResults->add($nonBlockingFailedTaskResult);
        $taskRunner->run($context)->willReturn($taskResults);

        $output->isDecorated()->willReturn(false);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(Argument::containingString('non blocking task message'))->shouldBeCalled();
        $output->writeln(Argument::any())->shouldBeCalled();

        $this->run($output, $context);
    }

    function it_should_add_a_progress_listener_during_run(
        OutputInterface $output,
        TaskRunner $taskRunner,
        ContextInterface $context,
        EventDispatcherInterface $eventDispatcher
    ) {
        $taskRunner->run($context)->willReturn(new TaskResultCollection());
        $eventDispatcher->addSubscriber(Argument::type(ProgressSubscriber::class))->shouldBeCalled();
        $this->run($output, $context)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }
}
