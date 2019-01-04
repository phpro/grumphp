<?php

namespace GrumPHPTest\Helper;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Console\Application;
use GrumPHP\Console\Helper\TaskRunnerHelper;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\ParallelTaskInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\Yaml\Yaml;

trait GrumPhpTestHelperTrait
{
    public function resolveContext(array $files = []): RunContext
    {
        return new RunContext(new FilesCollection($files));
    }

    /**
     * @param TaskRunnerContext|array|null $context
     * @return TaskRunnerContext
     */
    protected function resolveTaskRunnerContext($context = null): TaskRunnerContext
    {
        if ($context instanceof TaskRunnerContext) {
            return $context;
        }

        $files           = [];
        $tasks           = [];
        $testSuite       = null;
        $parallelOptions = null;
        if (is_array($context)) {
            $files           = $context["files"] ?? $files;
            $tasks           = $context["tasks"] ?? $tasks;
            $testSuite       = $context["testSuite"] ?? $testSuite;
            $parallelOptions = $context["parallelOptions"] ?? $parallelOptions;
        }

        $context = new RunContext(new FilesCollection($files));
        return new TaskRunnerContext($context, $tasks, $testSuite, $parallelOptions);
    }

    /**
     * @param array $config
     * @param bool $removeConfigFileAfterCreation
     * @return Application
     * @throws \ReflectionException
     */
    protected function resolveApplication(array $config, $removeConfigFileAfterCreation = true)
    {
        $backup = $_SERVER['argv'];
        $cwd    = getcwd();

        //TODO use a path outside of the repo, e.g. system_temp_dir for tmp config
        $appBaseDir                      = __DIR__."/../../";
        $config["parameters"]            = $config["parameters"] ?? [];
        $config["parameters"]["git_dir"] = $appBaseDir;
        $config["parameters"]["bin_dir"] = $appBaseDir."vendor/bin";

        $yaml = Yaml::dump($config, 10, 2);

        $reflector = new \ReflectionClass(get_called_class());
        // path to the file of the currently executed test
        $path     = $reflector->getFileName().".grumphp.yml";
        $dir      = dirname($path);
        $filename = basename($path);

        try {
            // create temporary config that can be resolved for the test
            file_put_contents($path, $yaml);
            // override cli input so that the config can be resolved
            $_SERVER['argv'] = [
                "grumphp",
                "--config=$filename",
            ];
            // change cwd - required because GrumPhp uses
            // the cwd to determine the config path
            chdir($dir);

            // "Extend" Application because this gives us a fully configured
            // Application.
            // Todo: Better provide a TestHelper that gets injected in dev context
            $app = new class($config) extends Application
            {
                protected $config;

                public function __construct(array $config)
                {
                    $this->config = $config;
                    parent::__construct();
                }

                public function getConfig(): array
                {
                    return $this->config;
                }

                /**
                 * @param $name
                 * @return TaskInterface|false
                 * @throws \Exception
                 */
                public function getTask($name)
                {
                    $runner = $this->getTaskRunner();
                    return $runner->getTasks()->filterByTaskNames([$name])->first();
                }

                /**
                 * @return TaskRunner
                 * @throws \Exception
                 */
                public function getTaskRunner()
                {
                    return $this->container->get("task_runner");
                }
            };

            return $app;
        } finally {
            // revert all changes
            $_SERVER['argv'] = $backup;
            chdir($cwd);
            if ($removeConfigFileAfterCreation && file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @param Application $app
     * @param array $taskData
     * @param TaskRunnerContext|array|null $context
     * @return TaskResultCollection
     */
    protected function runTasks(Application $app, array $taskData = [], $context = null): TaskResultCollection
    {
        $context = $this->resolveTaskRunnerContext($context);

        $r = $this->resolveTaskRunner($app, $taskData);
        return $r->run($context);
    }

    /**
     * @param Application $app
     * @param array $taskData
     * @param TaskRunnerContext|array|null $context
     * @param int|null $verbosity
     * @return string
     */
    protected function runTasksWithOutput(Application $app, array $taskData = [], $context = null, int $verbosity = null): string
    {
        $context   = $this->resolveTaskRunnerContext($context);
        $verbosity = $verbosity ?? OutputInterface::VERBOSITY_NORMAL;
        // this will also set the tasks up accordingly
        $this->resolveTaskRunner($app, $taskData);
        $runCommand = $app->find("run");
        $helper     = $runCommand->getHelper(TaskRunnerHelper::HELPER_NAME);

        $output = new BufferedOutput($verbosity);
        $helper->run($output, $context);
        return $output->fetch();
    }

    protected function resolveTaskRunnerHelper(Application $app): TaskRunnerHelper
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $helper = $app->getTaskRunnerHelper();
        return $helper;
    }

    protected function resolveTaskRunner(Application $app, array $taskData = []): TaskRunner
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $r = $app->getTaskRunner();
        if (!empty($taskData)) {
            $tasks = new TasksCollection();
            foreach ($taskData as $data) {
                /**
                 * @var TaskInterface $task
                 * @var array $meta
                 */
                $task = $data[0];
                $meta = $data[1] ?? [];
                $tasks->add($task);
                $meta = $meta + [
                        "priority" => 1,
                        "blocking" => true,
                    ];
                $this->registerInContainer(
                    $app,
                    "grumphp.tasks.metadata",
                    [
                        $task->getName() => $meta,
                    ]
                );
            }
            $this->setNonPublicProperty($r, "tasks", $tasks);
        }

        return $r;
    }

    protected function registerInContainer(Application $app, $key, $value, $append = true)
    {
        /**
         * @var ContainerBuilder $container
         */
        $container = $this->getNonPublicProperty($app, "container");
        /**
         * @var FrozenParameterBag $parameterBag
         */
        $parameterBag = $this->getNonPublicProperty($container, "parameterBag");
        $parameters   = $this->getNonPublicProperty($parameterBag, "parameters");

        // Note:
        // this function purely exists to satisfy the "no-else" code calythenics
        // maybe rethink the purpose of those... I highly doubt that this is more readable
        // than
        // if (is_array($current) && $append) {
        //     $parameters[$key] = array_merge($current, $value);
        // }else{
        //     $parameters[$key] = $value;
        // }

        $modifyParameters = function () use ($parameters, $append, $key, $value) {
            $current = $parameters[$key];
            if (is_array($current) && $append) {
                $parameters[$key] = array_merge($current, $value);
                return $parameters;
            }
            $parameters[$key] = $value;
            return $parameters;
        };

        $parameters = $modifyParameters();

        $this->setNonPublicProperty($parameterBag, "parameters", $parameters);
    }

    protected function resolveTask(
        Application $app,
        string $taskName,
        bool $overrideExecutablePathWithExecutableName = true
    ): TaskInterface {
        /**
         * @var ParallelTaskInterface $task
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $task = $app->getTask($taskName);
        if (!$task) {
            /** @noinspection PhpUndefinedMethodInspection */
            throw new \InvalidArgumentException("Task '$taskName' could not be found. Config:\n".var_export($app->getConfig(), true));
        }

        if ($overrideExecutablePathWithExecutableName) {
            $processBuilder         = $this->getNonPublicProperty($task, "processBuilder");
            $externalCommandLocator = new class extends ExternalCommand
            {
                /** @noinspection PhpMissingParentConstructorInspection */
                public function __construct()
                {
                }

                public function locate(string $command, bool $forceUnix = false): string
                {
                    return $command;
                }
            };
            $this->setNonPublicProperty($processBuilder, "externalCommandLocator", $externalCommandLocator);
        }

        return $task;
    }

    protected function getNonPublicProperty($object, $property)
    {
        $getter = function () use ($object, $property) {
            return $object->$property;
        };
        $getter = $getter->bindTo($object, $object);
        return $getter();
    }

    protected function setNonPublicProperty($object, $property, $value)
    {
        $setter = function () use ($object, $property, $value) {
            return $object->$property = $value;
        };
        $setter = $setter->bindTo($object, $object);
        $setter();
    }

    protected function assertTaskResults(array $expected, TaskResultCollection $actual)
    {
        $actual = $actual
            ->map(function (TaskResultInterface $result) {
                return [
                    "resultCode" => $result->getResultCode(),
                    "message"    => $result->getMessage(),
                ];
            })
            ->toArray()
        ;

        $this->assertEquals($expected, $actual, "Failed asserting task results.");
    }

    protected function assertRunOutput(string $expected, string $actual)
    {
        $this->assertEquals($expected, $actual, "Failed asserting run output, found:\n".$actual);
    }
}
