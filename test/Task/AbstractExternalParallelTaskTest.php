<?php

namespace GrumPHPTest\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\AbstractExternalParallelTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Paratest;
use GrumPHPTest\Helper\GrumPHPTestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class AbstractExternalParallelTaskTest extends TestCase
{
    use GrumPHPTestHelperTrait;

    protected function getApplication($config)
    {
        // register the TestTask in the config
        $services = [
            "services" => [
                "task.test_task" => [
                    "class"     => AbstractExternalParallelTestTask::class,
                    "arguments" => [
                        "@config",
                        "@process_builder",
                        "@formatter.raw_process",
                    ],
                    "tags"      => [
                        [
                            "name"   => "grumphp.task",
                            "config" => "test_task",
                        ],
                    ],
                ],
            ],
        ];
        return $this->resolveApplication($config + $services);
    }

    /**
     * @dataProvider getMetadata_dataProvider
     * @param array $config
     * @param array $expected
     */
    public function test_getMetadata(array $config, array $expected)
    {
        $app = $this->getApplication($config);
        /**
         * @var Paratest $task
         */
        $task   = $this->resolveTask($app, AbstractExternalParallelTestTask::getStaticName());
        $actual = $task->getStage();
        $this->assertSame($expected["stage"], $actual, "Stage not found");
        $actual = $task->getPassthru();
        $this->assertSame($expected["passthru"], $actual, "Stage not found");
    }

    /**
     * @return array
     */
    public function getMetadata_dataProvider()
    {
        return [
            "default (no metadata given)" => [
                "data"     => [
                    "parameters" => [
                        "tasks" => [
                            "test_task" => [
                            ],
                        ],
                    ],
                ],
                "expected" => [
                    "passthru" => "",
                    "stage"    => 0,
                ],
            ],
            "passthru and stage" => [
                "data"     => [
                    "parameters" => [
                        "tasks" => [
                            "test_task" => [
                                "metadata" => [
                                    "passthru" => "foo",
                                    "stage"    => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                "expected" => [
                    "passthru" => "foo",
                    "stage"    => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider buildProcess_dataProvider
     * @param array $config
     * @param string $expected
     * @throws \ReflectionException
     */
    public function test_buildProcess(array $config, string $expected)
    {
        $app = $this->getApplication($config);

        /**
         * @var Paratest $task
         */
        $task    = $this->resolveTask($app, AbstractExternalParallelTestTask::getStaticName());
        $context = $this->resolveContext();

        /**
         * @var Process $process
         */
        $process = $task->resolveProcess($context);

        $this->assertProcessCommand($expected, $process);
    }

    /**
     * @return array
     */
    public function buildProcess_dataProvider()
    {
        return [
            "default"       => [
                "data"     => [
                    "parameters" => [
                        "tasks" => [
                            "test_task" => [
                                'foo' => 'bar',
                            ],
                        ],
                    ],
                ],
                "expected" => "'test_task' '--foo=bar'",
            ],
            "with passthru" => [
                "data"     => [
                    "parameters" => [
                        "tasks" => [
                            "test_task" => [
                                'foo'      => 'bar',
                                "metadata" => [
                                    "passthru" => "'--version'",
                                ],
                            ],
                        ],
                    ],
                ],
                "expected" => "'test_task' '--foo=bar' '--version'",
            ],
        ];
    }
}

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
class AbstractExternalParallelTestTask extends AbstractExternalParallelTask
{

    public function getName(): string
    {
        return "test_task";
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'foo' => '.',
        ]);

        $resolver->addAllowedTypes('foo', ['string']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return true;
    }

    /**
     * Override in Task
     *
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    protected function buildArguments(
        string $command,
        array $config,
        ContextInterface $context
    ): ProcessArgumentsCollection {
        $arguments = $this->processBuilder->createArgumentsForCommand($command);
        $arguments->addRequiredArgument('--foo=%s', (string) $config['foo']);

        return $arguments;
    }
}
