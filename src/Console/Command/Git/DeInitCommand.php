<?php

declare(strict_types=1);

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

    public function __construct(GrumPHP $grumPHP, Filesystem $filesystem)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->filesystem = $filesystem;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    /**
     * @return int|void
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
        }

        $output->writeln('<fg=yellow>GrumPHP stopped sniffing your commits! Too bad ...<fg=yellow>');
    }

    protected function paths(): PathsHelper
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }
}
