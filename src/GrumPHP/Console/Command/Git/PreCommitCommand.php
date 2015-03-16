<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\ExceptionInterface;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Locator\LocatorInterface;
use GrumPHP\Runner\TaskRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

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

            $output->writeln('<fg=red>' . $this->getAsciiResource('failed') . '</fg=red>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</fg=red>');

            return 1;
        }

        $output->write('<fg=green>' . $this->getAsciiResource('succeeded') . '</fg=green>');

        return 0;
    }

    /**
     * @return Finder
     */
    protected function getCommittedFiles()
    {
        return $this->changedFilesLocator->locate();
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function getAsciiResource($name)
    {
        return file_get_contents(sprintf(__DIR__ . '/../../../../../resources/ascii/%s.txt', $name));
    }
}
