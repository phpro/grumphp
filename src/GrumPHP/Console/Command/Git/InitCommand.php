<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
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
    protected static $hooks = array(
        'pre-commit',
    );

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

    /**
     * @param GrumPHP $grumPHP
     * @param Filesystem $filesystem
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
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $gitHooksPath = $this->grumPHP->getGitDir() . '/.git/hooks/';
        $resourceHooksPath = __DIR__ . '/../../../../../resources/hooks/';

        foreach (self::$hooks as $hook) {
            $gitHook = $gitHooksPath . $hook;
            $hookTemplate = $resourceHooksPath . $hook;

            if (!$this->filesystem->exists($hookTemplate)) {
                throw new \RuntimeException(
                    sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate)
                );
            }

            $content = $this->parseHookBody($hook, $hookTemplate);
            file_put_contents($gitHook, $content);
            $this->filesystem->chmod($gitHook, 0775);
        }
    }

    /**
     * @param $hook
     * @param $templateFile
     *
     * @return mixed
     */
    protected function parseHookBody($hook, $templateFile)
    {
        $content = file_get_contents($templateFile);
        $replacements = array(
          '$(HOOK_COMMAND)' => $this->generateHookCommand('git:' . $hook),
        );

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * @param $command
     *
     * @return string
     */
    protected function generateHookCommand($command)
    {
        $this->processBuilder->setArguments(array(
            'php',
            $this->grumPHP->getBinDir() . '/grumphp',
            $command,
            '--config=' . $this->input->getOption('config'),
        ));

        return $this->processBuilder->getProcess()->getCommandLine();
    }
}
