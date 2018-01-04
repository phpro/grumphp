<?php declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Util\Filesystem;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

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
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var InputInterface
     */
    protected $input;

    public function __construct(GrumPHP $grumPHP, Filesystem $filesystem, ProcessBuilder $processBuilder)
    {
        parent::__construct();

        $this->grumPHP = $grumPHP;
        $this->filesystem = $filesystem;
        $this->processBuilder = $processBuilder;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $gitHooksPath = $this->paths()->getGitHooksDir();
        $resourceHooksPath = $this->paths()->getGitHookTemplatesDir() . $this->grumPHP->getHooksPreset();
        $resourceHooksPath = $this->paths()->getPathWithTrailingSlash($resourceHooksPath);
        $customHooksPath = $this->paths()->getPathWithTrailingSlash($this->grumPHP->getHooksDir());

        // Some git clients do not automatically create a git hooks folder.
        if (!$this->filesystem->exists($gitHooksPath)) {
            $this->filesystem->mkdir($gitHooksPath);
            $output->writeln(sprintf(
                '<fg=yellow>Created git hooks folder at: %s</fg=yellow>',
                $gitHooksPath
            ));
        }

        foreach (self::$hooks as $hook) {
            $gitHook = $gitHooksPath . $hook;
            $hookTemplate = new SplFileInfo($resourceHooksPath . $hook);
            if ($customHooksPath && $this->filesystem->exists($customHooksPath . $hook)) {
                $hookTemplate = new SplFileInfo($customHooksPath . $hook);
            }

            if (!$this->filesystem->exists($hookTemplate)) {
                throw new RuntimeException(
                    sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate)
                );
            }

            $content = $this->parseHookBody($hook, $hookTemplate);
            $this->filesystem->dumpFile($gitHook, $content);
            $this->filesystem->chmod($gitHook, 0775);
        }

        $output->writeln('<fg=yellow>Watch out! GrumPHP is sniffing your commits!<fg=yellow>');
    }

    /**
     * @return mixed
     */
    protected function parseHookBody($hook, SplFileInfo $templateFile)
    {
        $content = $this->filesystem->readFromFileInfo($templateFile);
        $replacements = [
            '${HOOK_EXEC_PATH}' => $this->paths()->getGitHookExecutionPath(),
            '$(HOOK_COMMAND)' => $this->generateHookCommand('git:' . $hook),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * @throws \GrumPHP\Exception\FileNotFoundException
     */
    protected function generateHookCommand($command): string
    {
        $executable = $this->paths()->getBinCommand('grumphp', true);
        $this->processBuilder->setArguments([
            $this->paths()->getRelativeProjectPath($executable),
            $command
        ]);

        if ($configFile = $this->useExoticConfigFile()) {
            $this->processBuilder->add(sprintf('--config=%s', $configFile));
        }

        return $this->processBuilder->getProcess()->getCommandLine();
    }

    /**
     * This method will tell you which exotic configuration file should be used.
     *
     * @return null|string
     */
    protected function useExoticConfigFile()
    {
        try {
            $configPath = $this->paths()->getAbsolutePath($this->input->getOption('config'));
            if ($configPath != $this->paths()->getDefaultConfigPath()) {
                return $this->paths()->getRelativeProjectPath($configPath);
            }
        } catch (FileNotFoundException $e) {
            // no config file should be set.
        }

        return null;
    }

    /**
     * @return PathsHelper
     */
    protected function paths(): PathsHelper
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }
}
