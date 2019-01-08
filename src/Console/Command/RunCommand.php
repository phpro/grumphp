<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\FileProvider\DefaultProvider;
use GrumPHP\FileProvider\FileProviderInterface;
use GrumPHP\Runner\ParallelOptions;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class RunCommand extends Command
{
    const COMMAND_NAME = 'run';

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var FileProviderInterface[]
     */
    protected $providers;

    public function __construct(GrumPHP $grumPHP, array $providers)
    {
        $this->grumPHP   = $grumPHP;
        $this->providers = $providers;
        parent::__construct();
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->addOption(
            'testsuite',
            null,
            InputOption::VALUE_REQUIRED,
            'Specify which testsuite you want to run.',
            null
        );
        $this->addOption(
            'tasks',
            null,
            InputOption::VALUE_REQUIRED,
            'Specify which tasks you want to run (comma separated). Example: --tasks=task1,task2',
            null
        );
        $this->addOption(
            'passthru',
            null,
            InputOption::VALUE_REQUIRED,
            'The given string is appended to the underlying external command. '.
            'Example: --passthru="--version --foo=bar"',
            null
        );
        $providerList = implode(",", array_keys($this->getProviders()));
        $this->addOption(
            'file-provider',
            null,
            InputOption::VALUE_REQUIRED,
            'The provider that resolves the files to check. Values: '.$providerList.'. '.
            'Example: --file-provider="changed"',
            DefaultProvider::NAME
        );
        $this->addArgument(
            'files',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            '(optional; overrides --file-provider) A list of files to be used. Example: file1 foo/bar/baz.php'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $files        = $input->getArgument("files") ?? [];
        $providerName = $input->getOption("file-provider");
        $files      = $this->resolveFiles($files, $providerName);
        $testSuites = $this->grumPHP->getTestSuites();

        $tasks           = $this->resolveTasks($input->getOption("tasks") ?? "");
        $parallelOptions = $this->resolveParallelOptions();
        $passthru        = $input->getOption("passthru") ?? "";

        $context = new TaskRunnerContext(
            new RunContext($files),
            $tasks,
            (bool) $input->getOption('testsuite') ? $testSuites->getRequired($input->getOption('testsuite')) : null,
            $parallelOptions,
            $passthru
        );

        $start   = microtime(true);
        $result  = $this->taskRunner()->run($output, $context);
        $runtime = sprintf("Runtime %0.2fs", microtime(true) - $start);
        if ($output->isVerbose()) {
            $output->writeln($runtime);
        }
        return $result;
    }

    protected function taskRunner(): TaskRunnerHelper
    {
        return $this->getHelper(TaskRunnerHelper::HELPER_NAME);
    }

    protected function paths(): PathsHelper
    {
        return $this->getHelper(PathsHelper::HELPER_NAME);
    }

    /**
     * @param string[] $files
     * @param string $providerName
     * @return FilesCollection
     */
    protected function resolveFiles(array $files, string $providerName): FilesCollection
    {
        if (count($files) > 0) {
            $collection = new FilesCollection();
            foreach ($files as $file) {
                $collection[] = new SplFileInfo($file, dirname($file), $file);
            }
            return $collection;
        }
        $provider = $this->resolveProvider($providerName);
        return $provider->getFiles();
    }

    /**
     * @param string $value
     * @return string[]
     */
    protected function resolveTasks(string $value): array
    {
        return Str::explodeWithCleanup(",", $value);
    }

    /**
     * @return FileProviderInterface[]
     */
    protected function getProviders(): array
    {
        return $this->providers;
    }

    protected function resolveProvider(string $providerName): FileProviderInterface
    {
        $allProviders = $this->getProviders();
        if (!array_key_exists($providerName, $allProviders)) {
            $valid = implode(",", array_keys($allProviders));
            throw new \InvalidArgumentException("Provider '$providerName' does not exist. Valid values: $valid");
        }
        return $allProviders[$providerName];
    }

    /**
     * @return ParallelOptions|null
     */
    protected function resolveParallelOptions()
    {
        if (!$this->grumPHP->runInParallel()) {
            return null;
        }
        return new ParallelOptions(
            $this->grumPHP->getParallelProcessWaitTime(),
            $this->grumPHP->getParallelProcessLimit()
        );
    }
}
