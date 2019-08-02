<?php

declare(strict_types=1);

namespace GrumPHP\Console;

use Symfony\Component\Console\Application;

class ApplicationConfigurator
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.15.2';

    /**
     * @var iterable
     */
    private $helpers;

    public function __construct(iterable $helpers)
    {
        $this->helpers = $helpers;
    }

    public function configure(Application $application): void
    {
        $application->setVersion(self::APP_VERSION);
        $application->setName(self::APP_NAME);
        $this->registerHelpers($application);
    }

    private function registerHelpers(Application $application): void
    {
        $helperSet = $application->getHelperSet();
        foreach ($this->helpers as $helper) {
            $helperSet->set($helper);
        }
    }
}
