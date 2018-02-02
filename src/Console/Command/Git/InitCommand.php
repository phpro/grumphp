<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Util\Filesystem;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is responsible for enabling all the configured hooks.
 */
class InitCommand extends Command
{
    const COMMAND_NAME = 'git:init';
    const BACKUP_HOOK_EXTENSION = '.backup';

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
     * @var InputInterface
     */
    protected $input;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @param GrumPHP        $grumPHP
     * @param Filesystem     $filesystem
     * @param ProcessBuilder $processBuilder
     */
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $gitHooksPath = $this->paths()->getGitHooksDir();
        $resourceHooksPath = $this->paths()->getGitHookTemplatesDir().$this->grumPHP->getHooksPreset();
        $resourceHooksPath = $this->paths()->getPathWithTrailingSlash($resourceHooksPath);
        $customHooksPath = $this->paths()->getPathWithTrailingSlash($this->grumPHP->getHooksDir());

        // Some git clients do not automatically create a git hooks folder.
        if (!$this->filesystem->exists($gitHooksPath)) {
            $this->filesystem->mkdir($gitHooksPath);
            $output->writeln(
                sprintf(
                    '<fg=yellow>Created git hooks folder at: %s</fg=yellow>',
                    $gitHooksPath
                )
            );
        }

        foreach (self::$hooks as $hook) {
            $gitHook = $gitHooksPath.$hook;
            $hookTemplate = new SplFileInfo($resourceHooksPath.$hook);
            if ($customHooksPath && $this->filesystem->exists($customHooksPath.$hook)) {
                $hookTemplate = new SplFileInfo($customHooksPath.$hook);
            }

            if (!$this->filesystem->exists($hookTemplate)) {
                throw new \RuntimeException(
                    sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate)
                );
            }

            $content = $this->parseHookBody($hook, $hookTemplate);

            if ($this->filesystem->exists($gitHook)) {
                $gitHookFile = new SplFileObject($gitHook);

                if (!$this->isGitHookFromGrumPhp($gitHookFile)) {
                    $this->saveOldGitHook($gitHook, $gitHookFile, $output);
                }
            }

            $this->filesystem->dumpFile($gitHook, $content);
            $this->filesystem->chmod($gitHook, 0775);
        }

        $output->writeln('<fg=yellow>Watch out! GrumPHP is sniffing your commits!<fg=yellow>');
    }

    /**
     * @param $hook
     * @param $templateFile
     *
     * @return mixed
     */
    protected function parseHookBody($hook, SplFileInfo $templateFile)
    {
        $content = $this->filesystem->readFromFileInfo($templateFile);
        $replacements = [
            '${HOOK_EXEC_PATH}' => $this->paths()->getGitHookExecutionPath(),
            '$(HOOK_COMMAND)' => $this->generateHookCommand('git:'.$hook),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * @param $command
     *
     * @return string
     * @throws \GrumPHP\Exception\FileNotFoundException
     */
    protected function generateHookCommand($command)
    {
        $configFile = $this->useExoticConfigFile();

        $arguments = $this->processBuilder->createArgumentsForCommand('grumphp');
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
    protected function paths()
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }

    /**
     * @param SplFileObject $hookFile
     *
     * @return bool
     */
    private function isGitHookFromGrumPhp(SplFileObject $hookFile)
    {
        return stripos($this->filesystem->readFromFileInfo($hookFile->getFileInfo()), 'grumphp') !== false;
    }

    /**
     * @param string          $hookPath
     * @param SplFileObject   $gitHookFile
     *
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    private function saveOldGitHook($hookPath, SplFileObject $gitHookFile, OutputInterface $output)
    {
        $fileName = $hookPath.self::BACKUP_HOOK_EXTENSION;
        $this->filesystem->dumpFile($fileName, $gitHookFile->fread($gitHookFile->getSize()));

        $output->writeln(
            sprintf(
                '<fg=yellow>Your old <fg=white>%s</fg=white> hook can be found at <fg=white>%s</fg=white>.</fg=yellow>',
                $gitHookFile->getFilename(),
                $fileName
            )
        );
    }
}
