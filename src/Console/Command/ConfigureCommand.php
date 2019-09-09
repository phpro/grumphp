<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use Exception;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
     * @var InputInterface
     */
    protected $input;

    public function __construct(GrumPHP $config, Filesystem $filesystem)
    {
        parent::__construct();

        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    /**
     * Configure command.
     */
    protected function configure(): void
    {
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

        // Search tasks
        $tasks = [];
        if ($input->isInteractive()) {
            $question = new ChoiceQuestion(
                'Which tasks do you want to run?',
                $this->config->getRegisteredTasks()
            );
            $question->setMultiselect(true);
            $tasks = (array) $helper->ask($input, $output, $question);
        }

        // Build configuration
        return [
            'parameters' => [
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
}
