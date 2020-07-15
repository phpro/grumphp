<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Environment;

use GrumPHP\Configuration\Model\EnvConfig;
use Symfony\Component\Dotenv\Dotenv;

class DotEnvRegistrar
{
    public static function register(EnvConfig $config): void
    {
        $env = new Dotenv();

        if ($config->hasFiles()) {
            $env->overload(...$config->getFiles());
        }

        if ($config->hasVariables()) {
            $env->populate($config->getVariables(), true);
        }
    }
}
