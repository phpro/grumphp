<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\TaskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 *
 * @package GrumPHP\Console\Command
 */
class PreCommitCommand extends Command
{

    const COMMAND_NAME = 'git:pre-commit';

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition(array(
                new InputOption('base-dir', 'b', InputOption::VALUE_OPTIONAL, '/.', getcwd()),
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
        $baseDir = $input->getOption('base-dir');

        $config = GrumPHP::loadFromComposerFile($baseDir);
        $taskManager = new TaskManager($config);

        $files = $this->getCommitedFiles($config->getGitDir());
        $taskManager->run($files);
    }

    /**
     * @param $gitDir
     *
     * @return array
     */
    protected function getCommitedFiles($gitDir)
    {
        $locator = new ChangedFiles($gitDir);
        return $locator->locate(ChangedFiles::PATTERN_PHP);
    }
}
