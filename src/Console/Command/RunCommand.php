<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Locator\RegisteredFiles;
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

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var RegisteredFiles
     */
    protected $registeredFilesLocator;

    public function __construct(GrumPHP $config, RegisteredFiles $registeredFilesLocator)
    {
        parent::__construct();

        $this->grumPHP = $config;
        $this->registeredFilesLocator = $registeredFilesLocator;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    /**
     * Configure command.
     */
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
        $files = $this->getRegisteredFiles();
        $testSuites = $this->grumPHP->getTestSuites();

        $tasks = Str::explodeWithCleanup(',', $input->getOption("tasks") ?? '');

        $context = new TaskRunnerContext(
            new RunContext($files),
            (bool) $input->getOption('testsuite') ? $testSuites->getRequired($input->getOption('testsuite')) : null,
            $tasks
        );

        return $this->taskRunner()->run($output, $context);
    }

    protected function getRegisteredFiles(): FilesCollection
    {
        return $this->registeredFilesLocator->locate();
    }

    protected function taskRunner(): TaskRunnerHelper
    {
        return $this->getHelper(TaskRunnerHelper::HELPER_NAME);
    }
}
