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
}
