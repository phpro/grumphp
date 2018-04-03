<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Locator\RegisteredFiles;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
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

    public function __construct(GrumPHP $grumPHP, RegisteredFiles $registeredFilesLocator)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->registeredFilesLocator = $registeredFilesLocator;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->addOption(
            'testsuite',
            null,
            InputOption::VALUE_REQUIRED,
            'Specify which testsuite you want to run.',
            null
        );
    }

    /**
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->getRegisteredFiles();
        $testSuites = $this->grumPHP->getTestSuites();

        $context = new TaskRunnerContext(
            new RunContext($files),
            (bool) $input->getOption('testsuite') ? $testSuites->getRequired($input->getOption('testsuite')) : null
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

    protected function paths(): PathsHelper
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }
}
