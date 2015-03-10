<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param GrumPHP $grumPHP
     * @param ContainerInterface $container
     */
    public function __construct(GrumPHP $grumPHP, ContainerInterface $container)
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        $this->addCommands(array(
            new Command\Git\InitCommand(
                $grumPHP,
                $container->get('filesystem'),
                $container->get('process_builder')
            ),
            new Command\Git\PreCommitCommand(
                $grumPHP,
                $container->get('task_runner'),
                $container->get('locator.changed_files'),
                $container->get('locator.external_command'),
                $container->get('process_builder')
            ),
        ));
    }
}
