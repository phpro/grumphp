<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Exception\ExceptionInterface;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param TaskRunner $taskRunner
     */
    public function __construct(TaskRunner $taskRunner)
    {
        $this->taskRunner = $taskRunner;
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
        try {
            $this->taskRunner->run($context);
        } catch (ExceptionInterface $e) {
            // We'll fail hard on any exception not generated in GrumPHP

            return $this->returnErrorMessage($output, $e->getMessage());
        }

        // Skip before returning any messages
        if ($skipSuccessOutput) {
            return self::CODE_SUCCESS;
        }

        return $this->returnSuccessMessage($output);

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
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
