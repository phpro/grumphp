<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Locator\RegisteredFiles;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    const COMMAND_NAME = 'run';
    const EXIT_CODE_OK = 0;
    const EXIT_CODE_NOK = 1;

    /**
     * @var TestSuiteCollection
     */
    private $testSuites;

    /**
     * @var RegisteredFiles
     */
    private $registeredFilesLocator;

    /**
     * @var TaskRunner
     */
    private $taskRunner;


    public function __construct(
        TestSuiteCollection $testSuites,
        RegisteredFiles $registeredFilesLocator,
        TaskRunner $taskRunner
    ) {
        parent::__construct();

        $this->testSuites = $testSuites;
        $this->registeredFilesLocator = $registeredFilesLocator;
        $this->taskRunner = $taskRunner;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->addOption(
            'testsuite',
            null,
            InputOption::VALUE_REQUIRED,
            'Specify which testsuite you want to run.',
            null
        );
        $this->addOption(
            'tasks',
            null,
            InputOption::VALUE_REQUIRED,
            'Specify which tasks you want to run (comma separated). Example --tasks=task1,task2',
            null
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $this->registeredFilesLocator->locate();
        $tasks = Str::explodeWithCleanup(',', $input->getOption('tasks') ?? '');

        $context = new TaskRunnerContext(
            new RunContext($files),
            (bool) $input->getOption('testsuite')
                ? $this->testSuites->getRequired($input->getOption('testsuite'))
                : null,
            $tasks
        );

        $results = $this->taskRunner->run($context);

        return $results->isFailed() ? self::EXIT_CODE_NOK : self::EXIT_CODE_OK;
    }
}
