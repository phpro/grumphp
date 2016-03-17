<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class TaskRunnerHelper
 *
 * @package GrumPHP\Console\Helper
 */
class TaskRunnerHelper extends Helper
{
    const HELPER_NAME = 'taskrunner';

    const CODE_SUCCESS = 0;
    const CODE_ERROR = 1;

    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $warningMessages = array();

    /**
     * @param TaskRunner $taskRunner
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TaskRunner $taskRunner, EventDispatcherInterface $eventDispatcher)
    {
        $this->taskRunner = $taskRunner;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return PathsHelper
     */
    private function paths()
    {
        return $this->getHelperSet()->get(PathsHelper::HELPER_NAME);
    }

    /**
     * @param OutputInterface  $output
     * @param ContextInterface $context
     * @param bool|false       $skipSuccessOutput
     *
     * @return int
     */
    public function run(OutputInterface $output, ContextInterface $context, $skipSuccessOutput = false)
    {
        // Make sure to add some default event listeners before running.
        $this->registerEventListeners($output, $context);

        $taskResults = $this->taskRunner->run($context);

        /** @var \GrumPHP\Runner\TaskResult $taskResult */
        foreach ($taskResults as $taskResult) {
            if ($taskResult->isPassed()) {
                continue;
            }

            if ($taskResult->isBlocking()) {
                $this->returnWarningMessages($output);
                return $this->returnErrorMessage($output, $taskResult->getMessage());
            }

            $this->addWarningMessage($taskResult->getMessage());
        }

        // Skip before returning any messages
        if ($skipSuccessOutput) {
            return self::CODE_SUCCESS;
        }

        $this->returnWarningMessages($output);
        return $this->returnSuccessMessage($output);
    }

    /**
     * @param OutputInterface  $output
     * @param ContextInterface $context
     */
    private function registerEventListeners(OutputInterface $output, ContextInterface $context)
    {
        $this->eventDispatcher->addSubscriber(new ProgressSubscriber($output, new ProgressBar($output)));
    }

    /**
     * @param OutputInterface $output
     * @param string          $errorMessage
     *
     * @return int
     */
    private function returnErrorMessage(OutputInterface $output, $errorMessage)
    {
        $failed = $this->paths()->getAsciiContent('failed');
        if ($failed) {
            $output->writeln('<fg=red>' . $failed . '</fg=red>');
        }

        $output->writeln('<fg=red>' . $errorMessage . '</fg=red>');
        $output->writeln(
            '<fg=yellow>To skip commit checks, add -n or --no-verify flag to commit command</fg=yellow>'
        );

        return self::CODE_ERROR;
    }

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    private function returnSuccessMessage(OutputInterface $output)
    {
        $succeeded = $this->paths()->getAsciiContent('succeeded');
        if ($succeeded) {
            $output->write('<fg=green>' . $succeeded . '</fg=green>');
        }

        return self::CODE_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     */
    private function returnWarningMessages($output)
    {
        foreach ($this->warningMessages as $warningMessage) {
            $output->writeln('<fg=yellow>' . $warningMessage . '</fg=yellow>');
        }
    }

    /**
     * @param string $message
     */
    private function addWarningMessage($message)
    {
        $this->warningMessages[] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
