<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Locator\GitHooksDirLocator;
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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GitHooksDirLocator
     */
    private $gitHooksDirLocator;

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    public function __construct(
        Filesystem $filesystem,
        GitHooksDirLocator $gitHooksDirLocator
    ) {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->gitHooksDirLocator = $gitHooksDirLocator;
    }

    /**
     * Configure command.
     */
    protected function configure(): void
    {
        $this->setDescription('Removes the commit hooks');
    }

    /**
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $gitHooksPath = $this->gitHooksDirLocator->locate();

        foreach (InitCommand::$hooks as $hook) {
            $hookPath = $gitHooksPath.$hook;
            if (!$this->filesystem->exists($hookPath)) {
                continue;
            }

            $this->filesystem->remove($hookPath);
        }

        $output->writeln('<fg=yellow>GrumPHP stopped sniffing your commits! Too bad ...<fg=yellow>');
    }
}
