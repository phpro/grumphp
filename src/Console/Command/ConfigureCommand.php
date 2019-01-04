<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use Composer\Config;
use Exception;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\ComposerHelper;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Util\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ConfigureCommand extends Command
{
    const COMMAND_NAME = 'configure';

    /**
     * @var GrumPHP
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var InputInterface
     */
    protected $input;

    public function __construct(GrumPHP $config, Filesystem $filesystem, Repository $repository)
    {
        parent::__construct();

        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->repository = $repository;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Forces overwriting the configuration file when it already exists.'
        );
    }

    /**
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $grumphpConfigName = $this->input->getOption('config');
        $force = $input->getOption('force');
        if ($this->filesystem->exists($grumphpConfigName) && !$force) {
            if ($input->isInteractive()) {
                $output->writeln('<fg=yellow>GrumPHP is already configured!</fg=yellow>');
            }

            return;
        }

        // Check configuration:
        $configuration = $this->buildConfiguration($input, $output);
        if (!$configuration) {
            $output->writeln('<fg=yellow>Skipped configuring GrumPHP. Using default configuration.</fg=yellow>');

            return;
        }

        // Check write action
        $written = $this->writeConfiguration($configuration);
        if (!$written) {
            $output->writeln('<fg=red>The configuration file could not be saved. Give me some permissions!</fg=red>');

            return;
        }

        if ($input->isInteractive()) {
            $output->writeln('<fg=green>GrumPHP is configured and ready to kick ass!</fg=green>');
        }
    }

    /**
     * This method will ask the developer for it's input and will result in a configuration array.
     */
    protected function buildConfiguration(InputInterface $input, OutputInterface $output): array
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $questionString = $this->createQuestionString(
            'Do you want to create a grumphp.yml file?',
            'Yes'
        );
        $question = new ConfirmationQuestion($questionString, true);
        if (!$helper->ask($input, $output, $question)) {
            return [];
        }

        // Search for git_dir
        $default = $this->guessGitDir();
        $questionString = $this->createQuestionString('In which folder is GIT initialized?', $default);
        $question = new Question($questionString, $default);
        $question->setValidator([$this, 'pathValidator']);
        $gitDir = $helper->ask($input, $output, $question);

        // Search for bin_dir
        $default = $this->guessBinDir();
        $questionString = $this->createQuestionString('Where can we find the executables?', $default);
        $question = new Question($questionString, $default);
        $question->setValidator([$this, 'pathValidator']);
        $binDir = $helper->ask($input, $output, $question);

        // Search tasks
        $tasks = [];
        if ($input->isInteractive()) {
            $question = new ChoiceQuestion(
                'Which tasks do you want to run?',
                $this->getAvailableTasks($this->config)
            );
            $question->setMultiselect(true);
            $tasks = (array) $helper->ask($input, $output, $question);
        }

        // Build configuration
        return [
            'parameters' => [
                'git_dir' => $gitDir,
                'bin_dir' => $binDir,
                'tasks' => array_map(function ($task) {
                    return null;
                }, array_flip($tasks)),
            ],
        ];
    }

    protected function createQuestionString(string $question, string $default = null, string $separator = ':'): string
    {
        return null !== $default ?
            sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $separator) :
            sprintf('<info>%s</info>%s ', $question, $separator);
    }

    protected function writeConfiguration(array $configuration): bool
    {
        try {
            $yaml = Yaml::dump($configuration);
            $grumphpConfigName = $this->input->getOption('config');
            $this->filesystem->dumpFile($grumphpConfigName, $yaml);

            return true;
        } catch (Exception $e) {
            // Fail silently and return false!
        }

        return false;
    }

    /**
     * Make a guess to the bin dir.
     */
    protected function guessBinDir(): string
    {
        $defaultBinDir = $this->config->getBinDir();
        if (!$this->composer()->hasConfiguration()) {
            return $defaultBinDir;
        }

        $config = $this->composer()->getConfiguration();
        if (!$config->has('bin-dir')) {
            return $defaultBinDir;
        }

        return $config->get('bin-dir', Config::RELATIVE_PATHS);
    }

    protected function guessGitDir(): string
    {
        $defaultGitDir = $this->config->getGitDir();
        try {
            $topLevel = $this->repository->run('rev-parse', ['--show-toplevel']);
        } catch (Exception $e) {
            return $defaultGitDir;
        }

        return rtrim($this->paths()->getRelativePath($topLevel), '/');
    }

    public function pathValidator($path): bool
    {
        if (!$this->filesystem->exists($path)) {
            throw new RuntimeException(sprintf('The path %s could not be found!', $path));
        }

        return $path;
    }

    /**
     * Return a list of all available tasks.
     */
    protected function getAvailableTasks(GrumPHP $config): array
    {
        return $config->getRegisteredTasks();
    }

    protected function paths(): PathsHelper
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }

    protected function composer(): ComposerHelper
    {
        return $this->getHelper(ComposerHelper::HELPER_NAME);
    }
}
