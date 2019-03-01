<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskRunnerHelperSpec extends ObjectBehavior
{
    function let(
        GrumPHP $config,
        TaskRunner $taskRunner,
        EventDispatcherInterface $eventDispatcher,
        HelperSet $helperSet,
        PathsHelper $pathsHelper,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext
    ) {
        $this->beConstructedWith($config, $taskRunner, $eventDispatcher);

        $helperSet->get(PathsHelper::HELPER_NAME)->willreturn($pathsHelper);
        $this->setHelperSet($helperSet);

        $runnerContext->getTaskContext()->willReturn($taskContext);
        $runnerContext->hasTestSuite()->willReturn(false);
        $runnerContext->skipSuccessOutput()->willReturn(false);

        $config->getAdditionalInfo()->willReturn(null);
        $config->hideCircumventionTip()->willReturn(false);
    }

    function it_is_a_console_helper()
    {
        $this->shouldHaveType(Helper::class);
    }

    function it_should_return_error_code_with_a_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('failed')->willReturn('ascii content');
        $taskResults = new TaskResultCollection();
        $taskResults->add(TaskResult::createPassed($task->getWrappedObject(), $taskContext->getWrappedObject()));
        $taskResults->add(TaskResult::createFailed($task->getWrappedObject(), $taskContext->getWrappedObject(), 'failed task message'));
        $taskRunner->run($runnerContext)->willReturn($taskResults);
        $this->run($output, $runnerContext)->shouldReturn(TaskRunnerHelper::CODE_ERROR);
    }

    function it_should_return_success_code_with_no_failed_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('succeeded')->willReturn('ascii content');
        $taskResults = new TaskResultCollection();
        $taskResults->add(TaskResult::createPassed($task->getWrappedObject(), $taskContext->getWrappedObject()));
        $taskRunner->run($runnerContext)->willReturn($taskResults);
        $this->run($output, $runnerContext)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_return_success_code_during_a_failed_of_a_nonblocking_task(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('succeeded')->willReturn('ascii content');
        $testResults = new TaskResultCollection();
        $testResults->add(TaskResult::createNonBlockingFailed($task->getWrappedObject(), $taskContext->getWrappedObject(), 'failed task message'));
        $taskRunner->run($runnerContext)->willReturn($testResults);
        $this->run($output, $runnerContext)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }

    function it_should_display_all_errors_of_failed_tasks(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('failed')->willReturn('ascii content');
        $taskResults = new TaskResultCollection();
        $taskResults->add(TaskResult::createFailed($task->getWrappedObject(), $taskContext->getWrappedObject(), 'failed task message'));
        $taskResults->add(TaskResult::createFailed($task->getWrappedObject(), $taskContext->getWrappedObject(), 'another failed task message'));
        $taskRunner->run($runnerContext)->willReturn($taskResults);

        $output->isDecorated()->willReturn(false);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(Argument::containingString('failed task message'))->shouldBeCalled();
        $output->writeln(Argument::containingString('another failed task message'))->shouldBeCalled();
        $output->writeln(Argument::any())->shouldBeCalled();

        $this->run($output, $runnerContext);
    }

    function it_should_display_warning_of_non_blocking_failed_tasks(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        ContextInterface $taskContext,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('succeeded')->willReturn('ascii content');
        $taskResults = new TaskResultCollection();
        $taskResults->add(TaskResult::createNonBlockingFailed($task->getWrappedObject(), $taskContext->getWrappedObject(), 'non blocking task message'));
        $taskRunner->run($runnerContext)->willReturn($taskResults);

        $this->run($output, $runnerContext);
    }

    function it_should_add_a_progress_listener_during_run(
        OutputInterface $output,
        TaskRunner $taskRunner,
        TaskRunnerContext $runnerContext,
        EventDispatcherInterface $eventDispatcher,
        PathsHelper $pathsHelper
    ) {
        $pathsHelper->getAsciiContent('succeeded')->willReturn('ascii content');
        $taskRunner->run($runnerContext)->willReturn(new TaskResultCollection());
        $eventDispatcher->addSubscriber(Argument::type(ProgressSubscriber::class))->shouldBeCalled();
        $this->run($output, $runnerContext)->shouldReturn(TaskRunnerHelper::CODE_SUCCESS);
    }
}
