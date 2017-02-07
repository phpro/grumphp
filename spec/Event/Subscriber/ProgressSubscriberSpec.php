<?php

namespace spec\GrumPHP\Event\Subscriber;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProgressSubscriberSpec extends ObjectBehavior
{
    function let(OutputInterface $output, ProgressBar $progressBar)
    {
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

    function it_starts_progress(ProgressBar $progressBar, RunnerEvent $event, TasksCollection $tasks)
    {
        $tasks->count()->willReturn(2);
        $event->getTasks()->willReturn($tasks);

        $progressBar->setFormat(Argument::type('string'))->shouldBeCalled();
        $progressBar->setOverwrite(false)->shouldBeCalled();
        $progressBar->setMessage(Argument::type('string'))->shouldBeCalled();
        $progressBar->start(2)->shouldBeCalled();

        $this->startProgress($event);
    }

    function it_should_advance_progress(ProgressBar $progressBar, TaskEvent $event, TaskInterface $task)
    {
        $event->getTask()->willReturn($task);

        $progressBar->setFormat(Argument::type('string'))->shouldBeCalled();
        $progressBar->setOverwrite(false)->shouldBeCalled();
        $progressBar->setMessage(Argument::type('string'))->shouldBeCalled();
        $progressBar->advance()->shouldBeCalled();

        $this->advanceProgress($event);
    }

    function it_finishes_progress(OutputInterface $output, ProgressBar $progressBar, RunnerEvent $event)
    {
        $progressBar->getProgress()->willReturn(1);
        $progressBar->getMaxSteps()->willReturn(1);

        $progressBar->setOverwrite(false)->shouldBeCalled();
        $progressBar->finish()->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $this->finishProgress($event);
    }

    function it_finishes_progress_early(OutputInterface $output, ProgressBar $progressBar, RunnerEvent $event)
    {
        $progressBar->getProgress()->willReturn(1);
        $progressBar->getMaxSteps()->willReturn(2);

        $progressBar->setFormat(Argument::type('string'))->shouldBeCalled();
        $progressBar->setMessage(Argument::type('string'))->shouldBeCalled();

        $progressBar->setOverwrite(false)->shouldBeCalled();
        $progressBar->finish()->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $this->finishProgress($event);
    }
}
