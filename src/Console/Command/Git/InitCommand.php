<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\Environment\DotEnvSerializer;
use GrumPHP\Configuration\Model\HooksConfig;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is responsible for enabling all the configured hooks.
 */
class InitCommand extends Command
{
    const COMMAND_NAME = 'git:init';

    /**
     * @var array
     */
    public static $hooks = [
        'pre-commit',
        'commit-msg',
    ];

    /**
     * @var HooksConfig
     */
    private $hooksConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var InputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $input;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(
        HooksConfig $hooksConfig,
        Filesystem $filesystem,
        ProcessBuilder $processBuilder,
        Paths $paths
    ) {
        parent::__construct();

        $this->hooksConfig = $hooksConfig;
        $this->filesystem = $filesystem;
        $this->processBuilder = $processBuilder;
        $this->paths = $paths;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->setDescription('Registers the Git hooks');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $gitHooksPath = $this->paths->getGitHooksDir();
        $resourceHooksPath = $this->filesystem->buildPath(
            $this->paths->getInternalGitHookTemplatesPath(),
            $this->hooksConfig->getPreset()
        );
        $customHooksPath = $this->hooksConfig->getDir();

        // Some git clients do not automatically create a git hooks folder.
        if (!$this->filesystem->exists($gitHooksPath)) {
            $this->filesystem->mkdir($gitHooksPath);
            $output->writeln(sprintf(
                '<fg=yellow>Created git hooks folder at: %s</fg=yellow>',
                $gitHooksPath
            ));
        }

        foreach (self::$hooks as $hook) {
            $gitHook = $this->filesystem->buildPath($gitHooksPath, $hook);
            $hookTemplate = $this->filesystem->guessFile(
                array_filter([
                    $customHooksPath,
                    $resourceHooksPath,
                ]),
                [$hook]
            );

            if (!$this->filesystem->exists($hookTemplate)) {
                throw new RuntimeException(
                    sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate)
                );
            }

            $content = $this->parseHookBody($hook, $hookTemplate);
            $this->filesystem->backupFile($gitHook, md5($content));
            $this->filesystem->dumpFile($gitHook, $content);
            $this->filesystem->chmod($gitHook, 0775);
        }

        $output->writeln('<fg=yellow>Watch out! GrumPHP is sniffing your commits!<fg=yellow>');

        return 0;
    }

    protected function parseHookBody(string $hook, string $templateFile): string
    {
        $content = $this->filesystem->readPath($templateFile);

        $replacements = [
            '${HOOK_EXEC_PATH}' => $this->paths->getProjectDirRelativeToGitDir(),
            '$(HOOK_COMMAND)' => $this->generateHookCommand('git:'.$hook),
        ];

        foreach ($this->hooksConfig->getVariables() as $key => $value) {
            $replacements[sprintf('$(%s)', $key)] = $this->parseHookVariable($key, $value);
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * @param string|array $value
     */
    private function parseHookVariable(string $key, $value): string
    {
        switch ($key) {
            case 'EXEC_GRUMPHP_COMMAND':
                return ProcessFactory::fromScalar($value)->getCommandLine();
            case 'ENV':
                return DotEnvSerializer::serialize($value);
            default:
                /** @var string $value */
                return (string) $value;
        }
    }

    /**
     * @throws \GrumPHP\Exception\FileNotFoundException
     */
    protected function generateHookCommand(string $command): string
    {
        $configFile = $this->useExoticConfigFile();

        $arguments = $this->processBuilder->createArgumentsForCommand(
            'grumphp',
            function (string $executablePath): string {
                return $this->proposeRelativeUnixPath($executablePath);
            }
        );
        $arguments->add($command);
        $arguments->addOptionalArgument('--config=%s', $configFile);

        $process = $this->processBuilder->buildProcess($arguments);

        return $process->getCommandLine();
    }

    /**
     * This method will tell you which exotic configuration file should be used.
     *
     * @return null|string
     */
    protected function useExoticConfigFile(): ?string
    {
        /** @var ?string $configPath */
        $configPath = $this->input->getOption('config');

        if (!$configPath) {
            // Auto discovereable ...
            return null;
        }

        if (!$this->filesystem->exists($configPath)) {
            return null;
        }

        return $this->proposeRelativeUnixPath($configPath);
    }

    /**
     * Always try to make paths relative against the project dir inside the git hooks.
     * If it is not possible: the full path will be used.
     */
    private function proposeRelativeUnixPath(string $path): string
    {
        return $this->filesystem->ensureUnixPath(
            $this->paths->makePathRelativeToProjectDirWhenInSubFolder($path)
        );
    }
}
