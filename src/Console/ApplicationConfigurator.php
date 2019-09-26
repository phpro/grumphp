<?php

declare(strict_types=1);

namespace GrumPHP\Console;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class ApplicationConfigurator
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.16.1';

    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var iterable
     */
    private $helpers;

    public function __construct(GrumPHP $config, iterable $helpers)
    {
        $this->config = $config;
        $this->helpers = $helpers;
    }

    public function configure(Application $application): void
    {
        $application->setVersion(self::APP_VERSION);
        $application->setName(self::APP_NAME);
        $this->registerInputDefinitions($application);
        $this->registerHelpers($application);
    }

    private function registerHelpers(Application $application): void
    {
        $helperSet = $application->getHelperSet();
        foreach ($this->helpers as $helper) {
            $helperSet->set($helper);
        }
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
