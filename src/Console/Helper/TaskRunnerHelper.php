<?php

declare(strict_types=1);

namespace GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\Subscriber\ProgressSubscriber;
use GrumPHP\Locator\AsciiLocator;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var AsciiLocator
     */
    private $asciiLocator;

    public function __construct(
        GrumPHP $config,
        TaskRunner $taskRunner,
        EventDispatcher $eventDispatcher,
        AsciiLocator $asciiLocator
    ) {
        $this->config = $config;
        $this->taskRunner = $taskRunner;
        $this->eventDispatcher = $eventDispatcher;
        $this->asciiLocator = $asciiLocator;
    }

    public function run(OutputInterface $output, TaskRunnerContext $context): int
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

            return $this->returnErrorMessages($output, $failed->getAllMessages(), $warnings->getAllMessages());
        }

        if ($context->skipSuccessOutput()) {
            $this->returnWarningMessages($output, $warnings->getAllMessages());

            return self::CODE_SUCCESS;
        }

        return $this->returnSuccessMessage($output, $warnings->getAllMessages());
    }

    private function registerEventListeners(OutputInterface $output): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->eventDispatcher->addSubscriber(new ProgressSubscriber($output));
    }

    private function returnErrorMessages(OutputInterface $output, array $errorMessages, array $warnings): int
    {
        $failed = $this->asciiLocator->locate('failed');
        if ($failed) {
            $output->writeln('<fg=red>'.$failed.'</fg=red>');
        }

        $this->returnWarningMessages($output, $warnings);

        foreach ($errorMessages as $errorMessage) {
            $output->writeln('<fg=red>'.$errorMessage.'</fg=red>');
        }

        if (!$this->config->hideCircumventionTip()) {
            $output->writeln(
                '<fg=yellow>To skip commit checks, add -n or --no-verify flag to commit command</fg=yellow>'
            );
        }

        $this->returnAdditionalInfo($output);

        return self::CODE_ERROR;
    }

    private function returnSuccessMessage(OutputInterface $output, array $warnings): int
    {
        $succeeded = $this->asciiLocator->locate('succeeded');
        if ($succeeded) {
            $output->write('<fg=green>'.$succeeded.'</fg=green>');
        }

        $this->returnWarningMessages($output, $warnings);
        $this->returnAdditionalInfo($output);

        return self::CODE_SUCCESS;
    }

    private function returnWarningMessages(OutputInterface $output, array $warningMessages): void
    {
        foreach ($warningMessages as $warningMessage) {
            $output->writeln('<fg=yellow>'.$warningMessage.'</fg=yellow>');
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function returnAdditionalInfo(OutputInterface $output): void
    {
        if (null !== $this->config->getAdditionalInfo()) {
            $output->writeln($this->config->getAdditionalInfo());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::HELPER_NAME;
    }
}
