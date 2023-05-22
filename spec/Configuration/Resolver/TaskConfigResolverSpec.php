<?php

namespace spec\GrumPHP\Configuration\Resolver;

use GrumPHP\Task\Config\ConfigOptionsResolver;
use const GrumPHP\Exception\TaskConfigResolverException;
use GrumPHP\Exception\TaskConfigResolverException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Configuration\Resolver\TaskConfigResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskConfigResolverSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TaskConfigResolver::class);
    }

    public function it_can_list_task_names(): void
    {
        $this->beConstructedWith([
            'task1' => get_class($this->mockTask()),
            'task2' => get_class($this->mockTask()),
        ]);
        $this->listAvailableTaskNames()->shouldBe(['task1', 'task2']);
    }

    public function it_can_resolve_config_for_task_without_metadata(): void
    {
        $task1 = $this->mockTask();
        $this->beConstructedWith([$taskName = 'task1' => get_class($task1)]);
        $this->resolve('task1', ['metadata' => ['label' => 'hello']])->shouldBe([
            'class' => get_class($task1),
        ]);
    }

    public function it_fetches_resolver_for_task(): void
    {
        $task1 = $this->mockTask();
        $this->beConstructedWith([$taskName = 'task1' => get_class($task1)]);
        $result = $this->fetchByName($taskName)->resolve([]);
        $result->shouldBeLike($task1::getConfigurableOptions()->resolve([]));
    }

    public function it_fails_when_task_is_unknown(): void
    {
        $this->beConstructedWith(['task1' => get_class($this->mockTask())]);
        $this->shouldThrow(TaskConfigResolverException::class)->duringFetchByName('task2');
    }

    public function it_fails_when_task_is_not_a_grumphp_task(): void
    {
        $this->beConstructedWith([
            'task1' => get_class(new class() {}),
            'task2' => 'Some\\Mega\\Unknown\\Class\\PLease\\Dont\\Create\\Me',
        ]);
        $this->shouldThrow(TaskConfigResolverException::class)->duringFetchByName('task1');
        $this->shouldThrow(TaskConfigResolverException::class)->duringFetchByName('task2');
    }

    private function mockTask(): TaskInterface
    {
        return new class implements TaskInterface
        {
            public static function getConfigurableOptions(): ConfigOptionsResolver
            {
                $options = new OptionsResolver();
                $options->setDefault('class', static::class);
                return ConfigOptionsResolver::fromOptionsResolver($options);
            }

            public function canRunInContext(ContextInterface $context): bool
            {
                return true;
            }

            public function run(ContextInterface $context): TaskResultInterface
            {
                return TaskResult::createPassed($this, $context);
            }

            public function getConfig(): TaskConfigInterface
            {
                return new EmptyTaskConfig();
            }

            public function withConfig(TaskConfigInterface $config): TaskInterface
            {
                return $this;
            }
        };
    }
}
