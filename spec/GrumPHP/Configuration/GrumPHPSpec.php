<?php

namespace spec\GrumPHP\Configuration;

use GrumPHP\Configuration\Compiler\TaskCompilerPass;
use GrumPHP\Configuration\ContainerFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GrumPHPSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Configuration\GrumPHP');
    }

    function it_knows_the_bin_dir(ContainerInterface $container)
    {
        $container->getParameter('bin_dir')->willReturn('./vendor/bin');
        $this->getBinDir()->shouldReturn('./vendor/bin');
    }

    function it_knows_the_git_dir(ContainerInterface $container)
    {
        $container->getParameter('git_dir')->willReturn('.');
        $this->getGitDir()->shouldReturn('.');
    }

    function it_provides_a_list_of_active_task_configurations(ContainerInterface $container)
    {
        $container->getParameter('tasks')->willReturn(array());
        $this->getTaskConfig()->shouldReturn(array());
    }

    function it_can_return_a_particular_task_configuration(ContainerInterface $container)
    {
        $container->getParameter('tasks')->willReturn(array('name' => array()));
        $this->getTaskConfig('name')->shouldReturn(array());
    }

    function it_should_return_empty_ascii_location_for_unknown_resources(ContainerInterface $container)
    {
        $container->getParameter('ascii')->willReturn(array());
        $this->getAsciiContentPath('success')->shouldReturn(null);
    }

    function it_should_return_the_ascii_location_for_known_resources(ContainerInterface $container)
    {
        $container->getParameter('ascii')->willReturn(array('success' => 'success'));
        $this->getAsciiContentPath('success')->shouldReturn('success');
    }

    function it_should_load_available_tasks_from_the_service_container(ContainerBuilder $container, TaskCompilerPass $taskCompiler)
    {
        $container = ContainerFactory::buildFromConfiguration(__DIR__.'/../../../resources/config/services.yml');
        $taskCompiler->process($container);
        $this->beConstructedWith($container);
        $this->getTasks()->shouldBeArray();
    }
}
