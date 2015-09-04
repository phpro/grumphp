<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Exception\ExceptionInterface;
use GrumPHP\Locator\LocatorInterface;
use GrumPHP\Runner\TaskRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
     * @var TaskRunner
     */
    protected $taskRunner;

    /**
     * @var LocatorInterface
     */
    protected $changedFilesLocator;

    /**
     * @param GrumPHP $grumPHP
     * @param TaskRunner $taskRunner
     * @param LocatorInterface $changedFilesLocator
     */
    public function __construct(GrumPHP $grumPHP, TaskRunner $taskRunner, LocatorInterface $changedFilesLocator)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->taskRunner = $taskRunner;
        $this->changedFilesLocator = $changedFilesLocator;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->taskRunner->run($this->getCommittedFiles());
        } catch (ExceptionInterface $e) {
            // We'll fail hard on any exception not generated in GrumPHP
            $failed = $this->paths()->getAsciiContent('failed');
            if ($failed) {
                $output->writeln('<fg=red>' . $failed . '</fg=red>');
            }

            $output->writeln('<fg=red>' . $e->getMessage() . '</fg=red>');
            $output->writeln(
                '<fg=yellow>To skip commit checks, add -n or --no-verify flag to commit command</fg=yellow>'
            );

            return 1;
        }

        $succeeded = $this->paths()->getAsciiContent('succeeded');
        if ($succeeded) {
            $output->write('<fg=green>' . $succeeded . '</fg=green>');
        }

        return 0;
    }

    /**
     * @return FilesCollection
     */
    protected function getCommittedFiles()
    {
        return $this->changedFilesLocator->locate();
    }

    /**
     * @return PathsHelper
     */
    protected function paths()
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }
}
