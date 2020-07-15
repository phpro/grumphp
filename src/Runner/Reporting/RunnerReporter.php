<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Reporting;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\AsciiLocator;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;

class RunnerReporter
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @var AsciiLocator
     */
    private $asciiLocator;

    /**
     * @var RunnerConfig
     */
    private $config;

    public function __construct(
        IOInterface $IO,
        AsciiLocator $asciiLocator,
        RunnerConfig $config
    ) {
        $this->IO = $IO;
        $this->asciiLocator = $asciiLocator;
        $this->config = $config;
    }

    public function start(TaskRunnerContext $context): void
    {
        $this->IO->write($this->IO->colorize(['GrumPHP is sniffing your code!'], 'yellow'));
        if ($context->getTestSuite()) {
            $this->IO->style()->note('Running testsuite: '.$context->getTestSuite()->getName());
        }
    }

    public function finish(TaskRunnerContext $context, TaskResultCollection $results): void
    {
        $this->blockStreams();

        // Stop on failure message:
        if ($context->getTasks()->count() !== $results->count()) {
            $this->IO->writeError($this->IO->colorize(['Aborted ...'], 'red'));
        }

        $warnings = $results->filterByResultCode(TaskResult::NONBLOCKING_FAILED);
        if ($results->isFailed()) {
            $failed = $results->filterByResultCode(TaskResult::FAILED);
            $this->reportErrorMessages($failed->getAllMessages(), $warnings->getAllMessages());
            return;
        }

        if ($context->skipSuccessOutput()) {
            $this->reportWarningMessages($warnings->getAllMessages());
            return;
        }

        $this->reportSuccessMessage($warnings->getAllMessages());
    }

    /**
     * AMP parallel unblocks stdout and stderr
     * This results in chopped of output
     * More info : https://github.com/amphp/parallel/issues/104
     */
    private function blockStreams(): void
    {
        stream_set_blocking(fopen('php://stdout', 'r+'), true);
        stream_set_blocking(fopen('php://stderr', 'r+'), true);
    }

    private function reportErrorMessages(array $errorMessages, array $warnings): void
    {
        $failed = $this->asciiLocator->locate('failed');
        if ($failed) {
            $this->IO->writeError(
                $this->IO->colorize([$failed], 'red')
            );
        }

        $this->reportWarningMessages($warnings);
        $this->reportFailedMessages($errorMessages, 'red');

        if (!$this->config->hideCircumventionTip()) {
            $this->IO->writeError(
                $this->IO->colorize(
                    ['To skip commit checks, add -n or --no-verify flag to commit command'],
                    'yellow'
                )
            );
        }

        $this->reportAdditionalInfo();
    }

    private function reportSuccessMessage(array $warnings): void
    {
        $succeeded = $this->asciiLocator->locate('succeeded');
        if ($succeeded) {
            $this->IO->write($this->IO->colorize([$succeeded], 'green'));
        }

        $this->reportWarningMessages($warnings);
        $this->reportAdditionalInfo();
    }

    private function reportWarningMessages(array $warningMessages): void
    {
        $this->reportFailedMessages($warningMessages, 'yellow');
    }

    private function reportFailedMessages(array $messages, string $color): void
    {
        foreach ($messages as $label => $message) {
            $this->IO->style()->title($label);
            $this->IO->writeError($this->IO->colorize([$message], $color));
        }
    }

    private function reportAdditionalInfo(): void
    {
        if (null !== $this->config->getAdditionalInfo()) {
            $this->IO->write([$this->config->getAdditionalInfo()]);
        }
    }
}
