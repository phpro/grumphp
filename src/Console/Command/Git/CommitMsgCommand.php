<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
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
     * @var Paths
     */
    private $paths;

    public function __construct(
        GrumPHP $config,
        ChangedFiles $changedFilesLocator,
        Filesystem $filesystem,
        Paths $paths
    ) {
        parent::__construct();

        $this->grumPHP = $config;
        $this->changedFilesLocator = $changedFilesLocator;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
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
        $this->setDescription('Executed by the commit-msg commit hook');
        $this->addOption('git-user', null, InputOption::VALUE_REQUIRED, 'The configured git user name.', '');
        $this->addOption('git-email', null, InputOption::VALUE_REQUIRED, 'The configured git email.', '');
        $this->addArgument('commit-msg-file', InputArgument::REQUIRED, 'The configured commit message file.');
    }

    /**
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ConsoleIO($input, $output);
        $files = $this->getCommittedFiles($io);
        $gitUser = $input->getOption('git-user');
        $gitEmail = $input->getOption('git-email');
        $commitMsgPath = $input->getArgument('commit-msg-file');

        if (!$this->filesystem->isAbsolutePath($commitMsgPath)) {
            $commitMsgPath = $this->filesystem->buildPath($this->paths->getGitWorkingDir(), $commitMsgPath);
        }

        $commitMsgFile = new SplFileInfo($commitMsgPath);
        $commitMsg = $this->filesystem->readFromFileInfo($commitMsgFile);

        $output->writeln('<fg=yellow>GrumPHP detected a commit-msg command.</fg=yellow>');

        $context = new TaskRunnerContext(
            new GitCommitMsgContext($files, $commitMsg, $gitUser, $gitEmail),
            $this->grumPHP->getTestSuites()->getOptional('git_commit_msg')
        );

        return $this->taskRunner()->run($output, $context);
    }

    protected function getCommittedFiles(ConsoleIO $io): FilesCollection
    {
        if ($stdin = $io->readCommandInput(STDIN)) {
            return $this->changedFilesLocator->locateFromRawDiffInput($stdin);
        }

        return $this->changedFilesLocator->locateFromGitRepository();
    }

    protected function taskRunner(): TaskRunnerHelper
    {
        return $this->getHelper(TaskRunnerHelper::HELPER_NAME);
    }
}
