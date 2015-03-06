<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
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
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDefinition(array(
                new InputOption('base-dir', 'b', InputOption::VALUE_OPTIONAL, '', getcwd()),
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

        $filesystem = new Filesystem();
        foreach (self::$hooks as $hook) {
            $gitHook = $config->getGitDir() . '/hooks/' . $hook;
            $hookTemplate = GRUMPHP_PATH . '/hooks/' . $hook;

            if (!$filesystem->exists($hookTemplate)) {
                throw new \RuntimeException(sprintf('Could not find hook template for %s.', $hook));
            }

            $content = $this->parseHookBody($config, $hook, $hookTemplate);
            file_put_contents($gitHook, $content);
        }
    }

    /**
     * @param $config
     * @param $templateFile
     * @param $hook
     *
     * @return mixed
     */
    protected function parseHookBody($config, $templateFile, $hook)
    {
        $content = file_get_contents($templateFile);
        $replacements = array(
          '$(HOOK_COMMAND)' => $this->generateHookCommand($config, 'git:' . $hook),
        );

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * @param GrumPHP $config
     * @param $command
     *
     * @return string
     */
    protected function generateHookCommand($config, $command)
    {
        $executable = $config->getBaseDir() . '/bin/grumphp';
        $builder = new ProcessBuilder(array('php', $executable, $command));
        $builder->add('--base-dir=' . $config->getBaseDir());

        return $builder->getProcess()->getCommandLine();
    }
}
