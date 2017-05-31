<?php

namespace spec\GrumPHP\Event\Subscriber;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProgressSubscriberSpec extends ObjectBehavior
{
    function let(OutputInterface $output)
    {
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->isDecorated()->willReturn(false);
        $output->getFormatter()->willReturn(new OutputFormatter());

        $progressBar = new ProgressBar($output->getWrappedObject());

        $this->beConstructedWith($output, $progressBar);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProgressSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_should_subscribe_to_events()
    {
        $this->getSubscribedEvents()->shouldBeArray();
    }

    function it_starts_progress(OutputInterface $output, RunnerEvent $event, TasksCollection $tasks)
    {
        $tasks->count()->willReturn(2);
        $event->getTasks()->willReturn($tasks);

        $output->write('<fg=yellow>GrumPHP is sniffing your code!</fg=yellow>')->shouldBeCalled();

        $this->startProgress($event);
    }

    function it_should_advance_progress(OutputInterface $output, TaskEvent $event, TaskInterface $task)
    {
        $this->beConstructedWith($output, $progress = new ProgressBar($output->getWrappedObject(), 2));

        $event->getTask()->willReturn($task);

        $output->writeln('')->shouldBeCalled();
        $output->write(Argument::containingString('Running task'))->shouldBeCalled();
        $output->write(Argument::containingString('1/2'))->shouldBeCalled();

        $this->advanceProgress($event);
    }

    function it_finishes_progress(OutputInterface $output, RunnerEvent $event)
    {
        $this->beConstructedWith($output, $progress = new ProgressBar($output->getWrappedObject(), 0));

        $output->writeln('')->shouldBeCalled();

        $this->finishProgress($event);
    }

    function it_finishes_progress_early(OutputInterface $output, RunnerEvent $event)
    {
        $this->beConstructedWith($output, $progress = new ProgressBar($output->getWrappedObject(), 2));
        $output->write('<fg=red>Aborted ...</fg=red>')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $this->finishProgress($event);
    }
}
