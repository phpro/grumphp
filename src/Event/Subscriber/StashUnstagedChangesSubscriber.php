<?php

declare(strict_types=1);

namespace GrumPHP\Event\Subscriber;

use Exception;
use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StashUnstagedChangesSubscriber implements EventSubscriberInterface
{
    /**
     * @var GrumPHP
     */
    private $grumPHP;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var bool
     */
    private $stashIsApplied = false;

    /**
     * @var bool
     */
    private $shutdownFunctionRegistered = false;

    public function __construct(GrumPHP $grumPHP, Repository $repository, IOInterface $io)
    {
        $this->grumPHP = $grumPHP;
        $this->repository = $repository;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        $events = [
            RunnerEvents::RUNNER_RUN => ['saveStash', 10000],
            RunnerEvents::RUNNER_COMPLETE => ['popStash', -10000],
            RunnerEvents::RUNNER_FAILED => ['popStash', -10000],
        ];

        // Backward compatibility layer for Symfony Console < 4.0.
        // @todo Replace with ConsoleEvents::EXCEPTION when bumping symfony/console to ^3.3
        //       (and maybe revert back to returning a simple array, as it was before?)
        $consoleErrorEvent = defined(ConsoleEvents::class.'::ERROR')
            ? ConsoleEvents::ERROR
            : ConsoleEvents::EXCEPTION;
        $events[$consoleErrorEvent] = ['handleErrors', -10000];

        return $events;
    }

    public function saveStash(RunnerEvent $e)
    {
        if (!$this->isStashEnabled($e->getContext())) {
            return;
        }

        $this->doSaveStash();
    }

    /**
     * @throws ProcessException
     */
    public function popStash(RunnerEvent $e)
    {
        if (!$this->isStashEnabled($e->getContext())) {
            return;
        }

        $this->doPopStash();
    }

    public function handleErrors()
    {
        if (!$this->grumPHP->ignoreUnstagedChanges()) {
            return;
        }

        $this->doPopStash();
    }

    /**
     * Check if there is a pending diff and stash the changes.
     *
     * @reurn void
     */
    private function doSaveStash()
    {
        $pending = $this->repository->getWorkingCopy()->getDiffPending();
        if (!\count($pending->getFiles())) {
            return;
        }

        try {
            $this->io->write(['<fg=yellow>Detected unstaged changes... Stashing them!</fg=yellow>']);
            $this->repository->run('stash', ['save', '--quiet', '--keep-index', uniqid('grumphp')]);
        } catch (Exception $e) {
            // No worries ...
            $this->io->write([sprintf('<fg=red>Failed stashing changes: %s</fg=red>', $e->getMessage())]);

            return;
        }

        $this->stashIsApplied = true;
        $this->registerShutdownHandler();
    }

    private function doPopStash()
    {
        if (!$this->stashIsApplied) {
            return;
        }

        try {
            $this->io->write(['<fg=yellow>Reapplying unstaged changes from stash.</fg=yellow>']);
            $this->repository->run('stash', ['pop', '--quiet']);
        } catch (Exception $e) {
            throw new RuntimeException(
                'The stashed changes could not be applied. Please run `git stash pop` manually!'
                .'More info: '.$e->__toString(),
                0,
                $e
            );
        }

        $this->stashIsApplied = false;
    }

    private function isStashEnabled(ContextInterface $context): bool
    {
        return $this->grumPHP->ignoreUnstagedChanges() && $context instanceof GitPreCommitContext;
    }

    /**
     * Make sure to fetch errors and pop the stash before crashing.
     */
    private function registerShutdownHandler()
    {
        if ($this->shutdownFunctionRegistered) {
            return;
        }

        $subscriber = $this;
        register_shutdown_function(function () use ($subscriber) {
            if (!$error = error_get_last()) {
                return;
            }

            // Don't fail on non-blcoking errors!
            if (\in_array($error['type'], [E_DEPRECATED, E_USER_DEPRECATED, E_CORE_WARNING, E_CORE_ERROR], true)) {
                return;
            }

            $subscriber->handleErrors();
        });

        $this->shutdownFunctionRegistered = true;
    }
}
