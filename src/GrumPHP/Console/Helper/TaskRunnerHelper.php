<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Runner\TaskResult;
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

        $warnings = $taskResults->filterByResultCode(TaskResult::NONBLOCKING_FAILED);
        if ($taskResults->isFailed()) {
            $failed = $taskResults->filterByResultCode(TaskResult::FAILED);
            return $this->returnErrorMessages($output, $failed->getAllMessages(), $warnings->getAllMessages());
        }

        if ($skipSuccessOutput) {
            $this->returnWarningMessages($output, $warnings->getAllMessages());
            return self::CODE_SUCCESS;
        }

        return $this->returnSuccessMessage($output, $warnings->getAllMessages());
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
     * @param array           $errorMessages
     *
     * @return int
     */
    private function returnErrorMessages(OutputInterface $output, array $errorMessages, array $warnings)
    {
        $failed = $this->paths()->getAsciiContent('failed');
        if ($failed) {
            $output->writeln('<fg=red>' . $failed . '</fg=red>');
        }

        $this->returnWarningMessages($output, $warnings);

        foreach ($errorMessages as $errorMessage) {
            $output->writeln('<fg=red>' . $errorMessage . '</fg=red>');
        }

        $output->writeln(
            '<fg=yellow>To skip commit checks, add -n or --no-verify flag to commit command</fg=yellow>'
        );

        return self::CODE_ERROR;
    }

    /**
     * @param OutputInterface $output
     *
     * @param array           $warnings
     *
     * @return int
     */
    private function returnSuccessMessage(OutputInterface $output, array $warnings)
    {
        $succeeded = $this->paths()->getAsciiContent('succeeded');
        if ($succeeded) {
            $output->write('<fg=green>' . $succeeded . '</fg=green>');
        }

        $this->returnWarningMessages($output, $warnings);

        return self::CODE_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param array $warningMessages
     */
    private function returnWarningMessages($output, array $warningMessages)
    {
        foreach ($warningMessages as $warningMessage) {
            $output->writeln('<fg=yellow>' . $warningMessage . '</fg=yellow>');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
