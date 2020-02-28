<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Reporting;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\AsciiLocator;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;

final class RunnerReporter
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
     * @var GrumPHP
     */
    private $config;

    public function __construct(
        IOInterface $IO,
        AsciiLocator $asciiLocator,
        GrumPHP $config
    ) {
        $this->IO = $IO;
        $this->asciiLocator = $asciiLocator;
        $this->config = $config;
    }

    public function start(TaskRunnerContext $context): void
    {
        $this->IO->write($this->wrapMessagesInColor(['GrumPHP is sniffing your code!'], 'yellow'));
        if ($context->getTestSuite()) {
            $this->IO->style()->note('Running testsuite: '.$context->getTestSuite()->getName());
        }
    }

    public function finish(TaskRunnerContext $context, TaskResultCollection $results): void
    {
        // Stop on failure message:
        if ($context->getTasks()->count() !== $results->count()) {
            $this->IO->writeError($this->wrapMessagesInColor(['Aborted ...'], 'red'));
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

    private function reportErrorMessages(array $errorMessages, array $warnings): void
    {
        $failed = $this->asciiLocator->locate('failed');
        if ($failed) {
            $this->IO->writeError(
                $this->wrapMessagesInColor([$failed], 'red')
            );
        }

        $this->reportWarningMessages($warnings);
        $this->IO->writeError(
            $this->wrapMessagesInColor($errorMessages, 'red')
        );

        if (!$this->config->hideCircumventionTip()) {
            $this->IO->writeError(
                $this->wrapMessagesInColor(
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
            $this->IO->write($this->wrapMessagesInColor([$succeeded], 'green'));
        }

        $this->reportWarningMessages($warnings);
        $this->reportAdditionalInfo();
    }

    private function reportWarningMessages(array $warningMessages): void
    {
        $this->IO->writeError(
            $this->wrapMessagesInColor($warningMessages, 'yellow')
        );
    }

    private function reportAdditionalInfo(): void
    {
        if (null !== $this->config->getAdditionalInfo()) {
            $this->IO->write([$this->config->getAdditionalInfo()]);
        }
    }

    private function wrapMessagesInColor(array $messages, string $color): array
    {
        return array_map(
            static function (string $message) use ($color) : string {
                return '<fg='.$color.'>'.$message.'</fg='.$color.'>';
            },
            $messages
        );
    }
}
