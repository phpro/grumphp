<?php

declare(strict_types=1);

namespace GrumPHP\Console;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class ApplicationConfigurator
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.17.2';

    /**
     * @var GrumPHP
     */
    private $config;

    public function __construct(GrumPHP $config)
    {
        $this->config = $config;
    }

    public function configure(Application $application): void
    {
        $application->setVersion(self::APP_VERSION);
        $application->setName(self::APP_NAME);
        $this->registerInputDefinitions($application);
    }

    private function registerInputDefinitions(Application $application): void
    {
        $definition = $application->getDefinition();
        $definition->addOption(
            new InputOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to config'
            )
        );
    }
}
