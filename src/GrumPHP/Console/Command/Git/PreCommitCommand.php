<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Locator\LocatorInterface;
use GrumPHP\TaskRunner;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

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
     * @var LocatorInterface
     */
    protected $externalCommandLocator;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @param GrumPHP $grumPHP
     * @param TaskRunner $taskRunner
     * @param LocatorInterface $changedFilesLocator
     * @param LocatorInterface $externalCommandLocator
     * @param ProcessBuilder $processBuilder
     */
    public function __construct(GrumPHP $grumPHP, TaskRunner $taskRunner, LocatorInterface $changedFilesLocator, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->taskRunner = $taskRunner;
        $this->changedFilesLocator = $changedFilesLocator;
        $this->externalCommandLocator = $externalCommandLocator;
        $this->processBuilder = $processBuilder;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition(array(
                new InputOption('base-dir', 'b', InputOption::VALUE_OPTIONAL, '.', getcwd()),
            ));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->grumPHP->getActiveTasks() as $taskName) {
            if (!$this->grumPHP->hasConfiguration($taskName)) {
                throw new RuntimeException(sprintf('The "%s" configuration is active, but its configuration was not found.', $taskName));
            }

            $configuration = $this->grumPHP->getConfiguration($taskName);
            $task = $configuration->buildTaskInstance($this->grumPHP, $this->externalCommandLocator, $this->processBuilder);

            $this->taskRunner->addTask($task);
        }

        try {
            $this->taskRunner->run($this->getCommittedFiles());
        } catch (\Exception $e) {
            $output->writeln('<fg=red>' . $this->getAsciiResource('failed') . '</fg=red>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</fg=red>');

            return 1;
        }

        $output->write('<fg=green>' . $this->getAsciiResource('succeeded') . '</fg=green>');

        return 0;
    }

    /**
     * @return array
     */
    protected function getCommittedFiles()
    {
        return $this->changedFilesLocator->locate(ChangedFiles::PATTERN_PHP);
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function getAsciiResource($name)
    {
        return file_get_contents(sprintf('%s/resources/ascii/%s.txt', $this->grumPHP->getBaseDir(), $name));
    }
}
