<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use Exception;
use GrumPHP\Configuration\Resolver\TaskConfigResolver;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
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
     * @var TaskConfigResolver
     */
    private $taskConfigResolver;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var InputInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $input;

    public function __construct(TaskConfigResolver $taskConfigResolver, Filesystem $filesystem, Paths $paths)
    {
        parent::__construct();

        $this->taskConfigResolver = $taskConfigResolver;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Forces overwriting the configuration file when it already exists.'
        );
        $this->addOption(
            'silent',
            null,
            InputOption::VALUE_NONE,
            'Only output what really matters.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $configFile = $this->paths->getConfigFile();
        $force = $input->getOption('force');
        if ($this->filesystem->exists($configFile) && !$force) {
            if (!$input->getOption('silent')) {
                $output->writeln('<fg=yellow>GrumPHP is already configured!</fg=yellow>');
            }

            return 0;
        }

        // Check configuration:
        $configuration = $this->buildConfiguration($input, $output);
        if (!$configuration) {
            $output->writeln('<fg=yellow>Skipped configuring GrumPHP. Using default configuration.</fg=yellow>');

            return 0;
        }

        // Check write action
        $written = $this->writeConfiguration($configFile, $configuration);
        if (!$written) {
            $output->writeln('<fg=red>The configuration file could not be saved. Give me some permissions!</fg=red>');

            return 1;
        }

        if (!$input->getOption('silent')) {
            $output->writeln('<fg=green>GrumPHP is configured and ready to kick ass!</fg=green>');
        }

        return 0;
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
                $this->createQuestionString('Which tasks do you want to run?', null, ''),
                $this->taskConfigResolver->listAvailableTaskNames()
            );
            $question->setMultiselect(true);
            $tasks = (array) $helper->ask($input, $output, $question);
        }

        // Build configuration
        return [
            'grumphp' => [
                'tasks' => array_map(function ($task) {
                    return null;
                }, array_flip($tasks)),
            ],
        ];
    }

    protected function createQuestionString(string $question, string $default = null, string $separator = ':'): string
    {
        return null !== $default ?
            sprintf('<fg=green>%s</fg=green> [<fg=yellow>%s</fg=yellow>]%s ', $question, $default, $separator) :
            sprintf('<fg=green>%s</fg=green>%s ', $question, $separator);
    }

    protected function writeConfiguration(string $configFile, array $configuration): bool
    {
        try {
            $yaml = Yaml::dump($configuration);
            $this->filesystem->dumpFile($configFile, $yaml);

            return true;
        } catch (Exception $e) {
            // Fail silently and return false!
        }

        return false;
    }
}
