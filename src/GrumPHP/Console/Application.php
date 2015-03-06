<?php

namespace GrumPHP\Console;

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
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
        $this->addCommands(array(
            new Command\Git\InitCommand(),
            new Command\Git\PreCommitCommand(),
        ));
    }
}