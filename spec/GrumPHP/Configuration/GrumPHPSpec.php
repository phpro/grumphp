<?php

namespace spec\GrumPHP\Configuration;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

    function it_knows_the_base_dir(ContainerInterface $container)
    {
        $container->getParameter('base_dir')->shouldBeCalled();
        $this->getBaseDir();
    }

    function it_knows_the_bin_dir(ContainerInterface $container)
    {
        $container->getParameter('bin_dir')->shouldBeCalled();
        $this->getBinDir();
    }

    function it_knows_the_git_dir(ContainerInterface $container)
    {
        $container->getParameter('git_dir')->shouldBeCalled();
        $this->getGitDir();
    }

    function it_provides_a_list_of_active_task_configurations(ContainerInterface $container)
    {
        $container->getParameter('tasks')->shouldBeCalled();
        $this->getTaskConfig();
    }

    function it_can_return_a_particular_task_configuration(ContainerInterface $container)
    {
        $container->getParameter('tasks.name')->shouldBeCalled();
        $this->getTaskConfig('name');
    }
}
