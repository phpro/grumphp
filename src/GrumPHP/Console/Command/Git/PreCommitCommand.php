<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command runs the git pre-commit hook.
 */
class PreCommitCommand extends Command
{
    const COMMAND_NAME = 'git:pre-commit';

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ChangedFiles
     */
    protected $changedFilesLocator;

    /**
     * @param GrumPHP $grumPHP
     * @param ChangedFiles $changedFilesLocator
     */
    public function __construct(GrumPHP $grumPHP, ChangedFiles $changedFilesLocator)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->changedFilesLocator = $changedFilesLocator;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->addOption(
            'skip-success-output',
            null,
            InputOption::VALUE_NONE,
            'Skips the success output. This will be shown by another command in the git commit hook chain.'
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->changedFilesLocator->setLogger();
        }

        $files = $this->getCommittedFiles();
        $context = new GitPreCommitContext($files);
        $skipSuccessOutput = (bool) $input->getOption('skip-success-output');

        $output->writeln('<fg=yellow>GrumPHP detected a pre-commit command.</fg=yellow>');
        return $this->taskRunner()->run($output, $context, $skipSuccessOutput);
    }

    /**
     * @return FilesCollection
     */
    protected function getCommittedFiles()
    {
        return $this->changedFilesLocator->locate();
    }

    /**
     * @return TaskRunnerHelper
     */
    protected function taskRunner()
    {
        return $this->getHelper(TaskRunnerHelper::HELPER_NAME);
    }

    /**
     * @return PathsHelper
     */
    protected function paths()
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }
}
