<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\IO\IOFactory;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Locator\StdInFiles;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
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

    private IOInterface $io;

    public function __construct(
        TestSuiteCollection $testSuites,
        StdInFiles $stdInFilesLocator,
        ChangedFiles $changedFilesLocator,
        TaskRunner $taskRunner,
        IOInterface $io
    ) {
        parent::__construct();

        $this->testSuites = $testSuites;
        $this->stdInFilesLocator = $stdInFilesLocator;
        $this->changedFilesLocator = $changedFilesLocator;
        $this->taskRunner = $taskRunner;
        $this->io = $io;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->setDescription('Executed by the pre-commit hook');
        $this->addOption(
            'skip-success-output',
            null,
            InputOption::VALUE_NONE,
            'Skips the success output. This will be shown by another command in the git commit hook chain.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $this->getCommittedFiles();

        $context = (
            new TaskRunnerContext(
                new GitPreCommitContext($files),
                $this->testSuites->getOptional('git_pre_commit')
            )
        )->withSkippedSuccessOutput((bool) $input->getOption('skip-success-output'));

        $output->writeln('<fg=yellow>GrumPHP detected a pre-commit command.</fg=yellow>');

        $results = $this->taskRunner->run($context);

        return $results->isFailed() ? self::EXIT_CODE_NOK : self::EXIT_CODE_OK;
    }

    protected function getCommittedFiles(): FilesCollection
    {
        if ($stdin = $this->io->readCommandInput(STDIN)) {
            return $this->stdInFilesLocator->locate($stdin);
        }

        return $this->changedFilesLocator->locateFromGitRepository();
    }
}
