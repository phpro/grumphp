<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

class DeprecatedException extends RuntimeException
{
    public static function directParameterConfiguration(string $key): self
    {
        return new self(
            'Direct configuration of parameter '.$key.' is not allowed anymore.'.PHP_EOL.
            'Please rename the `parameters` section in your grumphp.yaml file to `grumphp`.'.PHP_EOL.
            'More info: https://github.com/phpro/grumphp/releases/tag/v0.19.0'.PHP_EOL.PHP_EOL
        );
    }
}
