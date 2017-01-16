<?php

namespace spec\GrumPHP\Event\Subscriber;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Repository;
use Gitonomy\Git\WorkingCopy;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\Subscriber\StashUnstagedChangesSubscriber;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StashUnstagedChangesSubscriberSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, Repository $repository, IOInterface $io, WorkingCopy $workingCopy, Diff $unstaged)
    {
        $grumPHP->ignoreUnstagedChanges()->willReturn(true);
        $repository->getWorkingCopy()->willReturn($workingCopy);
        $workingCopy->getDiffPending()->willReturn($unstaged);
        $unstaged->getFiles()->willReturn(['file1.php']);

        $this->beConstructedWith($grumPHP, $repository, $io);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StashUnstagedChangesSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_should_subscribe_to_events()
    {
        $this->getSubscribedEvents()->shouldBeArray();
    }

    function it_should_not_run_when_disabled(GrumPHP $grumPHP, Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());
        $grumPHP->ignoreUnstagedChanges()->willReturn(false);

        $this->saveStash($event);
        $this->popStash($event);

        $repository->run(Argument::cetera())->shouldNotBeCalled();
    }

    function it_should_not_run_in_invalid_context(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new RunContext(new FilesCollection()), new TaskResultCollection());

        $this->saveStash($event);
        $this->popStash($event);

        $repository->run(Argument::cetera())->shouldNotBeCalled();
    }

    function it_should_not_run_when_there_are_no_unstaged_changes(Repository $repository, Diff $unstaged)
    {
        $event = new RunnerEvent(new TasksCollection(), new RunContext(new FilesCollection()), new TaskResultCollection());
        $unstaged->getFiles()->willReturn([]);

        $this->saveStash($event);
        $this->popStash($event);

        $repository->run(Argument::cetera())->shouldNotBeCalled();
    }

    function it_should_not_try_to_pop_when_stash_saving_failed(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());

        $repository->run('stash', Argument::containing('save'))->willThrow('Exception');
        $repository->run('stash', Argument::containing('pop'))->shouldNotBeCalled();

        $this->saveStash($event);
        $this->popStash($event);
    }

    function it_should_display_exception_when_pop_fails(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->willThrow('Exception');

        $this->saveStash($event);
        $this->shouldThrow(RuntimeException::class)->duringPopStash($event);
    }

    function it_should_stash_changes(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();

        $this->saveStash($event);
    }

    function it_should_pop_changes(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->shouldBeCalled();

        $this->saveStash($event);
        $this->popStash($event);
    }

    function it_should_pop_changes_when_an_error_occurs(Repository $repository)
    {
        $event = new RunnerEvent(new TasksCollection(), new GitPreCommitContext(new FilesCollection()), new TaskResultCollection());

        $repository->run('stash', Argument::containing('save'))->shouldBeCalled();
        $repository->run('stash', Argument::containing('pop'))->shouldBeCalled();

        $this->saveStash($event);
        $this->handleErrors();
    }
}
