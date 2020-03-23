<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
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
     * @var Paths
     */
    private $paths;

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    public function __construct(
        Filesystem $filesystem,
        Paths $paths
    ) {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    protected function configure(): void
    {
        $this->setDescription('Removes the commit hooks');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $gitHooksPath = $this->paths->getGitHooksDir();

        foreach (InitCommand::$hooks as $hook) {
            $hookPath = $this->filesystem->buildPath($gitHooksPath, $hook);
            if (!$this->filesystem->exists($hookPath)) {
                continue;
            }

            $this->filesystem->remove($hookPath);
        }

        $output->writeln('<fg=yellow>GrumPHP stopped sniffing your commits! Too bad ...<fg=yellow>');

        return 0;
    }
}
