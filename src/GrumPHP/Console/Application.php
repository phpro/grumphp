<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\GrumPHP;
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
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @param string $name
     * @param string $version
     * @param GrumPHP $grumPHP
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', GrumPHP $grumPHP)
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        $this->addCommands(array(
            new Command\Git\InitCommand($grumPHP),
            new Command\Git\PreCommitCommand($grumPHP),
        ));
    }
}
