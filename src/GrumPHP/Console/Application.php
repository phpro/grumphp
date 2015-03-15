<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use Symfony\Component\Console\Application as SymfonyConsole;

/**
 * Class Application
 *
 * @package GrumPHP\Console
 */
class Application extends SymfonyConsole
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.1.0';

    /**
     * Set up application:
     */
    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        $container = $this->createContainer();

        $this->addCommands(array(
            new Command\Git\InitCommand(
                $container->get('config'),
                $container->get('filesystem'),
                $container->get('process_builder')
            ),
            new Command\Git\PreCommitCommand(
                $container->get('config'),
                $container->get('task_runner'),
                $container->get('locator.changed_files'),
                $container->get('locator.external_command'),
                $container->get('process_builder')
            ),
        ));
    }

    /**
     * TODO: make this part configurable with the 'config' option.
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainer()
    {
        $container = ContainerFactory::buildFromConfiguration(getcwd() . '/grumphp.yml');
        return $container;
    }
}
