<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\TaskManager;
use RuntimeException;
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
     * @var TaskManager
     */
    protected $taskManager;

    /**
     * @param GrumPHP $grumPHP
     * @param TaskManager $taskManager
     */
    public function __construct(GrumPHP $grumPHP, TaskManager $taskManager)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->taskManager = $taskManager;
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
            $task = $configuration->buildTaskInstance($this->grumPHP);

            $this->taskManager->addTask($task);
        }

        try {
            $files = $this->getCommittedFiles($this->grumPHP->getGitDir());
            $this->taskManager->run($files);
        } catch (\Exception $e) {
            $output->writeln('<fg=red>' . $this->getAsciiResource('failed') . '</fg=red>');
            $output->writeln('<fg=red>' . $e->getMessage() . '</fg=red>');

            return 1;
        }

        $output->write('<fg=green>' . $this->getAsciiResource('succeeded') . '</fg=green>');

        return 0;
    }

    /**
     * @param $gitDir
     *
     * @return array
     */
    protected function getCommittedFiles($gitDir)
    {
        $locator = new ChangedFiles($gitDir);
        return $locator->locate(ChangedFiles::PATTERN_PHP);
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
