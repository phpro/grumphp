<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Locator\ChangedFiles;
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

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ChangedFiles
     */
    protected $changedFilesLocator;

    public function __construct(GrumPHP $config, ChangedFiles $changedFilesLocator)
    {
        parent::__construct();

        $this->grumPHP = $config;
        $this->changedFilesLocator = $changedFilesLocator;
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
        $io = new ConsoleIO($input, $output);
        $files = $this->getCommittedFiles($io);

        $context = new TaskRunnerContext(
            new GitPreCommitContext($files),
            $this->grumPHP->getTestSuites()->getOptional('git_pre_commit')
        );
        $context->setSkipSuccessOutput((bool) $input->getOption('skip-success-output'));

        $output->writeln('<fg=yellow>GrumPHP detected a pre-commit command.</fg=yellow>');

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
