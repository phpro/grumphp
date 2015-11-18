<?php

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
    public static $hooks = array(
        'pre-commit',
        'commit-msg',
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
        $gitHooksPath = $this->paths()->getGitHooksDir();
        $resourceHooksPath = $this->paths()->getGitHookTemplatesDir();
        $helper = $this->getHelper('question');

        // Some git clients to not automatically create a git hooks folder.
        if (!$this->filesystem->exists($gitHooksPath)) {
            $this->filesystem->mkdir($gitHooksPath);
            $output->writeln(sprintf(
                '<fg=yellow>Created git hooks folder at: %s</fg=yellow>',
                $gitHooksPath
            ));
        }

        foreach (self::$hooks as $hook) {
            $gitHook = $gitHooksPath . $hook;
            $hookTemplate = $resourceHooksPath . $hook;

            if ($this->filesystem->exists($hookTemplate)) {
                $questionString = $this->createQuestionString(
                    sprintf('Hook for %s already exits, replace?', $hook),
                    'Yes'
                );

                $question = new Question($questionString, 'Yes');
                $replace = $helper->ask($input, $output, $question);

                if (preg_match('/[n]/i', $replace)) {
                    $output->writeln(
                        '<fg=yellow>We suggest creating a custom task with the contents of '.$hook.'</fg=yellow>'
                    );
                    $output->writeln(
                        '<fg=blue>More info: https://github.com/phpro/grumphp#custom-tasks</fg=blue>'
                    );

                    continue;
                }
            }

            if (!$this->filesystem->exists($hookTemplate)) {
                throw new \RuntimeException(
                    sprintf('Could not find hook template for %s at %s.', $hook, $hookTemplate)
                );
            }

            $content = $this->parseHookBody($hook, $hookTemplate);
            file_put_contents($gitHook, $content);
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
    protected function parseHookBody($hook, $templateFile)
    {
        $content = file_get_contents($templateFile);
        $replacements = array(
            '${HOOK_EXEC_PATH}' => $this->paths()->getGitHookExecutionPath(),
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
        $configPath = rtrim($this->paths()->getRelativePath($this->input->getOption('config')), '/\\');
        $this->processBuilder->setArguments(array(
            $this->paths()->getBinCommand('grumphp', true),
            $command,
            '--config=' . $configPath,
        ));

        return $this->processBuilder->getProcess()->getCommandLine();
    }

    /**
     * @return PathsHelper
     */
    protected function paths()
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }

    /**
     * @param        $question
     * @param null   $default
     * @param string $separator
     *
     * @return string
     */
    protected function createQuestionString($question, $default = null, $separator = ':')
    {
        return $default !== null ?
            sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $separator) :
            sprintf('<info>%s</info>%s ', $question, $separator);
    }
}
