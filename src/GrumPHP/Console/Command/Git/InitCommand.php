<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class InitCommand
 *
 * @package GrumPHP\Console\Command
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
     * @param GrumPHP $grumPHP
     */
    public function __construct(GrumPHP $grumPHP)
    {
        parent::__construct(null);

        $this->grumPHP = $grumPHP;
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
        $filesystem = $this->grumPHP->getContainer()->get('filesystem');
        foreach (self::$hooks as $hook) {
            $gitHook = $this->grumPHP->getGitDir() . '/.git/hooks/' . $hook;
            $hookTemplate = GRUMPHP_PATH . '/resources/hooks/' . $hook;

            if (!$filesystem->exists($hookTemplate)) {
                throw new \RuntimeException(sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate));
            }

            $content = $this->parseHookBody($hook, $hookTemplate);
            file_put_contents($gitHook, $content);
            $filesystem->chmod($gitHook, 0775);
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
        $executable = $this->grumPHP->getBaseDir() . '/vendor/bin/grumphp';
        $builder = new ProcessBuilder(array('php', $executable, $command));
        $builder->add('--base-dir=' . $this->grumPHP->getBaseDir());

        return $builder->getProcess()->getCommandLine();
    }
}
