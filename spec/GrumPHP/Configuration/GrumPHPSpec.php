<?php

namespace spec\GrumPHP\Configuration;

use GrumPHP\Configuration\Compiler\TaskCompilerPass;
use GrumPHP\Configuration\ContainerFactory;
use GrumPHP\Configuration\GrumPHP;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @mixin GrumPHP
 */
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

    function it_knows_the_hooks_dir(ContainerInterface $container)
    {
        $container->getParameter('hooks_dir')->willReturn('./hooks/');
        $this->getHooksDir()->shouldReturn('./hooks/');
    }

    function it_knows_the_hook_preset(ContainerInterface $container)
    {
        $container->getParameter('hooks_preset')->willReturn('local');
        $this->getHooksPreset()->shouldReturn('local');
    }

    function it_knows_to_stop_on_failure(ContainerInterface $container)
    {
        $container->getParameter('stop_on_failure')->willReturn(true);
        $this->stopOnFailure()->shouldReturn(true);
    }

    function it_knows_to_ignore_unstaged_changes(ContainerInterface $container)
    {
        $container->getParameter('ignore_unstaged_changes')->willReturn(true);
        $this->ignoreUnstagedChanges()->shouldReturn(true);
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

    function it_should_know_all_registered_tasks(ContainerInterface $container)
    {
        $container->getParameter('grumphp.tasks.registered')->willReturn(array('phpspec'));

        $this->getRegisteredTasks()->shouldBe(array('phpspec'));
    }

    function it_should_know_task_configuration(ContainerInterface $container)
    {
        $container->getParameter('grumphp.tasks.configuration')->willReturn(array('phpspec' => array()));

        $this->getTaskConfiguration('phpspec')->shouldReturn(array());
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringGetTaskConfiguration('phpunit');
    }

    function it_should_know_task_metadata(ContainerInterface $container)
    {
        $container->getParameter('grumphp.tasks.metadata')->willReturn(array('phpspec' => array()));

        $this->getTaskMetadata('phpspec')->shouldReturn(array());
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringGetTaskMetadata('phpunit');
    }

    function it_should_know_if_a_task_is_a_blocking_task(ContainerInterface $container)
    {
        $container->getParameter('grumphp.tasks.metadata')
            ->willReturn(
                array(
                    'phpspec' => array('blocking' => true),
                    'phpunit' => array('blocking' => false),
                )
            );
        $this->isBlockingTask('phpunit')->shouldReturn(false);
        $this->isBlockingTask('phpspec')->shouldReturn(true);
    }
}
