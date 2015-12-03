<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Task\Context\GitCommitMsgContext;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command runs the git commit-msg hook.
 */
class CommitMsgCommand extends Command
{
    const COMMAND_NAME = 'git:commit-msg';

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
        $this->addOption('git-user', null, InputOption::VALUE_REQUIRED, 'The configured git user name.', '');
        $this->addOption('git-email', null, InputOption::VALUE_REQUIRED, 'The configured git email.', '');
        $this->addArgument('commit-msg-file', InputArgument::REQUIRED, 'The configured commit message file.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->getCommittedFiles();
        $gitUser = $input->getOption('git-user');
        $gitEmail = $input->getOption('git-email');
        $commitMsgPath = $input->getArgument('commit-msg-file');
        $commitMsgFile = new SplFileInfo($commitMsgPath);

        $output->writeln('<fg=yellow>GrumPHP detected a commit-msg command.</fg=yellow>');
        $context = new GitCommitMsgContext($files, $commitMsgFile, $gitUser, $gitEmail);
        return $this->taskRunner()->run($output, $context);
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
