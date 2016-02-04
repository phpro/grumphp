<?php

namespace GrumPHP\Event\Subscriber;

use Exception;
use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StashUnstagedChangesSubscriber
 *
 * @package GrumPHP\Event\Subscriber
 */
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
     * @var bool
     */
    private $stashIsApplied = false;

    /**
     * @var bool
     */
    private $shutdownFunctionRegistered = false;

    /**
     * @param GrumPHP    $grumPHP
     * @param Repository $repository
     */
    public function __construct(GrumPHP $grumPHP, Repository $repository)
    {
        $this->grumPHP = $grumPHP;
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            RunnerEvents::RUNNER_RUN => 'saveStash',
            RunnerEvents::RUNNER_COMPLETE => 'popStash',
            RunnerEvents::RUNNER_FAILED => 'popStash',
            ConsoleEvents::EXCEPTION => 'handleErrors',
        );
    }

    /**
     * @param RunnerEvent $e
     *
     * @return void
     */
    public function saveStash(RunnerEvent $e)
    {
        if (!$this->isStashEnabled($e->getContext())) {
            return;
        }

        $this->doSaveStash();
    }

    /**
     * @param RunnerEvent $e
     *
     * @return void
     * @throws ProcessException
     */
    public function popStash(RunnerEvent $e)
    {
        if (!$this->isStashEnabled($e->getContext()) || !$this->stashIsApplied) {
            return;
        }

        $this->doPopStash();
    }

    /**
     * @return void
     */
    public function handleErrors()
    {
        if (!$this->stashIsApplied || !$this->grumPHP->ignoreUnstagedChanges()) {
            return;
        }

        $this->doPopStash();
    }

    /**
     *
     * @reurn void
     */
    private function doSaveStash()
    {
        try {
            $this->repository->run('stash', array('save', '--quiet', '--keep-index', uniqid('grumphp')));
        } catch (Exception $e) {
            // No worries ...
            return;
        }

        $this->stashIsApplied = true;
        $this->registerShutdownHandler();
    }

    /**
     * @return void
     */
    private function doPopStash()
    {
        try {
            $this->repository->run('stash', array('pop', '--quiet'));
        } catch (Exception $e) {
            throw new RuntimeException(
                'The stashed changes could not be applied. Please run `git stash pop` manually!'
                . 'More info: ' . $e->__toString(),
                0,
                $e
            );
        }

        $this->stashIsApplied = false;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    private function isStashEnabled(ContextInterface $context)
    {
        return $this->grumPHP->ignoreUnstagedChanges() && $context instanceof GitPreCommitContext;
    }

    /**
     * Make sure to fetch errors and pop the stash before crashing
     *
     * @return void
     */
    private function registerShutdownHandler()
    {
        if ($this->shutdownFunctionRegistered) {
            return;
        }

        $subscriber = $this;
        register_shutdown_function(function () use ($subscriber) {
            if (!error_get_last()) {
                return;
            }

            $subscriber->handleErrors();
        });

        $this->shutdownFunctionRegistered = true;
    }
}
