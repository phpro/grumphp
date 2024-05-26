<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Locator\RegisteredFiles;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\GitPrePushContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command runs the git pre-push hook.
 */
class PrePushCommand extends Command
{
    const COMMAND_NAME = 'git:pre-push';
    const EXIT_CODE_OK = 0;
    const EXIT_CODE_NOK = 1;
    const SHA1_EMPTY = '0000000000000000000000000000000000000000';

    public function __construct(
        private readonly TestSuiteCollection $testSuites,
        private readonly TaskRunner $taskRunner,
        private readonly GitRepository $repository,
        private readonly RegisteredFiles $registeredFilesLocator,
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->setDescription('Executed by the pre-push hook');
        $this->addOption(
            'skip-success-output',
            null,
            InputOption::VALUE_NONE,
            'Skips the success output. This will be shown by another command in the git commit hook chain.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $this->getChangedFiles();
        if ($files === null) {
            return self::EXIT_CODE_OK;
        }

        $context = (
            new TaskRunnerContext(
                new GitPrePushContext($files),
                $this->testSuites->getOptional('git_pre_push')
            )
        )->withSkippedSuccessOutput((bool) $input->getOption('skip-success-output'));

        $output->writeln('<fg=yellow>GrumPHP detected a pre-push command.</fg=yellow>');

        $results = $this->taskRunner->run($context);

        return $results->isFailed() ? self::EXIT_CODE_NOK : self::EXIT_CODE_OK;
    }

    protected function getChangedFiles(): ?FilesCollection
    {
        $fileCollection = new FilesCollection([]);

        // Loop over the commits.
        while ($commit = trim((string) fgets(STDIN))) {
            [$localRef, $localSha, , $remoteSha] = explode(' ', $commit);

            if ($localRef === '(delete)' || $localSha === self::SHA1_EMPTY) {
                // A branch has been deleted or there's no local branch.
                return null;
            }

            if ($remoteSha === self::SHA1_EMPTY) {
                // Do a full check if this is a new branch.
                return $this->registeredFilesLocator->locate();
            }

            $args = ['--no-commit-id', '--name-only', '-r', $localSha, $remoteSha];
            $command = (string) $this->repository->run('diff-tree', $args);

            foreach (explode("\n", $command) as $file) {
                // Avoid empty lines, missing files, and duplicates.
                if (!empty($file) && file_exists($file) && !$fileCollection->containsKey($file)) {
                      $fileCollection->set($file, new \SplFileInfo($file));
                }
            }
        }

        return $fileCollection;
    }
}
