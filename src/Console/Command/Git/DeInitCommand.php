<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is responsible for removing all the configured hooks.
 */
class DeInitCommand extends Command
{
    const COMMAND_NAME = 'git:deinit';

    /**
     * @var array
     */
    protected static $hooks = [
        'pre-commit',
        'commit-msg',
    ];

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param GrumPHP $grumPHP
     * @param Filesystem $filesystem
     */
    public function __construct(GrumPHP $grumPHP, Filesystem $filesystem)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->filesystem = $filesystem;
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
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \LogicException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $gitHooksPath = $this->paths()->getGitHooksDir();

        foreach (InitCommand::$hooks as $hook) {
            $hookPath = $gitHooksPath.$hook;
            if (!$this->filesystem->exists($hookPath)) {
                continue;
            }

            $this->filesystem->remove($hookPath);

            if ($this->filesystem->exists($hookPath.InitCommand::BACKUP_HOOK_EXTENSION)) {
                $this->restoreBackedupGitHook($output, $hookPath, $hook);
            }
        }

        $output->writeln('<fg=yellow>GrumPHP stopped sniffing your commits! Too bad ...</fg=yellow>');
    }

    /**
     * @return PathsHelper
     */
    protected function paths()
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }

    /**
     * @param OutputInterface $output
     * @param string          $hookPath
     * @param string          $hook
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \LogicException
     */
    private function restoreBackedupGitHook(OutputInterface $output, $hookPath, $hook)
    {
        $backupFile = new \SplFileObject($hookPath.InitCommand::BACKUP_HOOK_EXTENSION);

        $this->filesystem->dumpFile($hookPath, $this->filesystem->readFromFileInfo($backupFile));
        $this->filesystem->remove($backupFile->getRealPath());

        $output->writeln(
            sprintf(
                '<fg=yellow>GrumPHP detected a backup for <fg=white>%s</fg=white> hook and restored it.</fg=yellow>',
                $hook
            )
        );
    }
}
