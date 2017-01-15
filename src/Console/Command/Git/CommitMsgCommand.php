<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Util\Filesystem;
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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param GrumPHP      $grumPHP
     * @param ChangedFiles $changedFilesLocator
     * @param Filesystem   $filesystem
     */
    public function __construct(GrumPHP $grumPHP, ChangedFiles $changedFilesLocator, Filesystem $filesystem)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->changedFilesLocator = $changedFilesLocator;
        $this->filesystem = $filesystem;
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
        $io = new ConsoleIO($input, $output);
        $files = $this->getCommittedFiles($io);
        $gitUser = $input->getOption('git-user');
        $gitEmail = $input->getOption('git-email');
        $commitMsgPath = $input->getArgument('commit-msg-file');
        $commitMsgFile = new SplFileInfo($commitMsgPath);
        $commitMsg = $this->filesystem->readFromFileInfo($commitMsgFile);

        $output->writeln('<fg=yellow>GrumPHP detected a commit-msg command.</fg=yellow>');

        $context = new TaskRunnerContext(
            new GitCommitMsgContext($files, $commitMsg, $gitUser, $gitEmail),
            $this->grumPHP->getTestSuites()->getOptional('git_commit_msg')
        );

        return $this->taskRunner()->run($output, $context);
    }

    /**
     * @return FilesCollection
     */
    protected function getCommittedFiles(ConsoleIO $io)
    {
        if ($stdin = $io->readCommandInput(STDIN)) {
            return $this->changedFilesLocator->locateFromRawDiffInput($stdin);
        }

        return $this->changedFilesLocator->locateFromGitRepository();
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
