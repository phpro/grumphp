<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Git\GitRepository;
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
        private TestSuiteCollection $testSuites,
        private TaskRunner $taskRunner,
        private GitRepository $repository,
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

    protected function getChangedFiles(): FilesCollection
    {
        $fileCollection = new FilesCollection([]);
        $fullCheck = false;

        // Loop over the commits.
        while ($commit = trim((string) fgets(STDIN))) {
            [$localRef, $localSha, , $remoteSha] = explode(' ', $commit);

            // Skip if we are deleting a branch or if there is no local branch.
            if ($localRef === '(delete)' || $localSha === self::SHA1_EMPTY) {
                return $fileCollection;
            }

            // Do a full check if this is a new branch.
            if ($remoteSha === self::SHA1_EMPTY) {
                $fullCheck = true;
                break;
            }

            $command = (string) $this->repository->run('diff-tree', [
                '--no-commit-id',
                '--name-only',
                '-r',
                $localSha,
                $remoteSha,
            ]);

            // Filter out empty lines.
            $files = array_filter(array_map('trim', explode("\n", $command)));
            foreach ($files as $file) {
                // Avoid missing files and duplicates.
                if (file_exists($file) && !$fileCollection->containsKey($file)) {
                      $fileCollection->set($file, new \SplFileInfo($file));
                }
            }
        }

        if ($fileCollection->isEmpty() && !$fullCheck) {
            return $fileCollection;
        }

        return $fileCollection;
    }
}
