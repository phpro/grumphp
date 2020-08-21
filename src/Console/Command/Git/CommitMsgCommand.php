<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Locator\StdInFiles;
use GrumPHP\Runner\TaskRunner;
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
    const EXIT_CODE_OK = 0;
    const EXIT_CODE_NOK = 1;

    /**
     * @var TestSuiteCollection
     */
    private $testSuites;

    /**
     * @var StdInFiles
     */
    private $stdInFilesLocator;

    /**
     * @var ChangedFiles
     */
    private $changedFilesLocator;

    /**
     * @var TaskRunner
     */
    private $taskRunner;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(
        TestSuiteCollection $testSuites,
        StdInFiles $stdInFilesLocator,
        ChangedFiles $changedFilesLocator,
        TaskRunner $taskRunner,
        Filesystem $filesystem,
        Paths $paths
    ) {
        parent::__construct();

        $this->testSuites = $testSuites;
        $this->changedFilesLocator = $changedFilesLocator;
        $this->taskRunner = $taskRunner;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
        $this->stdInFilesLocator = $stdInFilesLocator;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->setDescription('Executed by the commit-msg commit hook');
        $this->addOption('git-user', null, InputOption::VALUE_REQUIRED, 'The configured git user name.', '');
        $this->addOption('git-email', null, InputOption::VALUE_REQUIRED, 'The configured git email.', '');
        $this->addArgument('commit-msg-file', InputArgument::REQUIRED, 'The configured commit message file.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ConsoleIO($input, $output);
        $files = $this->getCommittedFiles($io);

        /** @var string $gitUser */
        $gitUser = $input->getOption('git-user');

        /** @var string $gitEmail */
        $gitEmail = $input->getOption('git-email');

        /** @var string $commitMsgPath */
        $commitMsgPath = $input->getArgument('commit-msg-file');

        if (!$this->filesystem->isAbsolutePath($commitMsgPath)) {
            $commitMsgPath = $this->filesystem->buildPath($this->paths->getGitWorkingDir(), $commitMsgPath);
        }

        $commitMsgFile = new SplFileInfo($commitMsgPath);
        $commitMsg = $this->filesystem->readFromFileInfo($commitMsgFile);

        $output->writeln('<fg=yellow>GrumPHP detected a commit-msg command.</fg=yellow>');

        $context = new TaskRunnerContext(
            new GitCommitMsgContext($files, $commitMsg, $gitUser, $gitEmail),
            $this->testSuites->getOptional('git_commit_msg')
        );

        $results = $this->taskRunner->run($context);

        return $results->isFailed() ? self::EXIT_CODE_NOK : self::EXIT_CODE_OK;
    }

    protected function getCommittedFiles(ConsoleIO $io): FilesCollection
    {
        if ($stdin = $io->readCommandInput(STDIN)) {
            return $this->stdInFilesLocator->locate($stdin);
        }

        return $this->changedFilesLocator->locateFromGitRepository();
    }
}
