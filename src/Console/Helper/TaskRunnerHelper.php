<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private function getPathsHelper()
    {
        return $this->getHelperSet()->get(PathsHelper::HELPER_NAME);
    }

    /**
     * @param OutputInterface  $output
     * @param TaskRunnerContext $context
     *
     * @return int
     */
    public function run(OutputInterface $output, TaskRunnerContext $context)
    {
        // Make sure to add some default event listeners before running.
        $this->registerEventListeners($output);

        if ($context->hasTestSuite()) {
            $output->writeln(sprintf(
                '<fg=yellow>Running testsuite: %s</fg=yellow>',
                $context->getTestSuite()->getName()
            ));
        }

        $taskResults = $this->taskRunner->run($context);
        $warnings = $taskResults->filterByResultCode(TaskResult::NONBLOCKING_FAILED);

        if ($taskResults->isFailed()) {
            $failed = $taskResults->filterByResultCode(TaskResult::FAILED);
            $this->printErrorMessages($output, $failed->getAllMessages(), $warnings->getAllMessages());

            $context->shouldHideCircumventionTip() || $this->printCircumventionTip($output);

            return self::CODE_ERROR;
        }

        if ($context->shouldSkipSuccessOutput()) {
            $this->printWarningMessages($output, $warnings->getAllMessages());
        }

        if (!$context->shouldSkipSuccessOutput()) {
            $this->printSuccessMessage($output, $warnings->getAllMessages());
        }

        return self::CODE_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     */
    private function registerEventListeners(OutputInterface $output)
    {
        $this->eventDispatcher->addSubscriber(new ProgressSubscriber($output, new ProgressBar($output)));
    }

    /**
     * @param OutputInterface $output
     * @param array $errorMessages
     * @param array $warnings
     */
    private function printErrorMessages(OutputInterface $output, array $errorMessages, array $warnings)
    {
        $failed = $this->getPathsHelper()->getAsciiContent('failed');
        if ($failed) {
            $output->writeln('<fg=red>' . $failed . '</fg=red>');
        }

        $this->printWarningMessages($output, $warnings);

        foreach ($errorMessages as $errorMessage) {
            $output->writeln('<fg=red>' . $errorMessage . '</fg=red>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param array           $warnings
     */
    private function printSuccessMessage(OutputInterface $output, array $warnings)
    {
        $succeeded = $this->getPathsHelper()->getAsciiContent('succeeded');
        if ($succeeded) {
            $output->write('<fg=green>' . $succeeded . '</fg=green>');
        }

        $this->printWarningMessages($output, $warnings);
    }

    /**
     * @param OutputInterface $output
     * @param array $warningMessages
     */
    private function printWarningMessages(OutputInterface $output, array $warningMessages)
    {
        foreach ($warningMessages as $warningMessage) {
            $output->writeln('<fg=yellow>' . $warningMessage . '</fg=yellow>');
        }
    }

    private function printCircumventionTip(OutputInterface $output)
    {
        $output->writeln(
            '<fg=yellow>To skip commit checks add -n or --no-verify flag to commit command</fg=yellow>'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
