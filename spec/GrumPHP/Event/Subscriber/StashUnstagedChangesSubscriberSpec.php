<?php

namespace spec\GrumPHP\Event\Subscriber;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StashUnstagedChangesSubscriberSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, Repository $repository)
    {
        $grumPHP->ignoreUnstagedChanges()->willReturn(true);
        $this->beConstructedWith($grumPHP, $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Event\Subscriber\StashUnstagedChangesSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_should_subscribe_to_events()
    {
        $this->getSubscribedEvents()->shouldBeArray();
    }

    function it_should_not_run_when_disabled(GrumPHP $grumPHP, Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));
        $grumPHP->ignoreUnstagedChanges()->willReturn(false);

        $this->saveStash($event);
        $this->popStash($event);

        $repository->run(Argument::cetera())->shouldNotBeCalled();
    }

    function it_should_not_run_in_invalid_context(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new RunContext(new FilesCollection()));

        $this->saveStash($event);
        $this->popStash($event);

        $repository->run(Argument::cetera())->shouldNotBeCalled();
    }

    function it_should_not_try_to_pop_when_stash_saving_failed(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));

        $repository->run('stash', Argument::containing('save'))->willThrow('Exception');
        $repository->run('stash', Argument::containing('pop'))->shouldNotBeCalled();

        $this->saveStash($event);
        $this->popStash($event);
    }

    function it_should_display_exception_when_pop_fails(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->willThrow('Exception');

        $this->saveStash($event);
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringPopStash($event);
    }

    function it_should_stash_changes(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();

        $this->saveStash($event);
    }

    function it_should_pop_changes(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->shouldBeCalled();

        $this->saveStash($event);
        $this->popStash($event);
    }

    function it_should_pop_changes_when_an_error_occurs(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()));

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->shouldBeCalled();

        $this->saveStash($event);
        $this->handleErrors();
    }
}
