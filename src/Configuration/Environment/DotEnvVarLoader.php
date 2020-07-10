<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Environment;

class DotEnvVarLoader
{
    /**
     * @return array
     */
    public static function load(): array
    {
        $setByGrumphp = array_filter(explode(',', $_SERVER['SYMFONY_DOTENV_VARS'] ?? ''));

        return array_filter(
            $_SERVER,
            function ($value, string $key) use ($setByGrumphp) {
                if (strpos($key, 'GRUMPHP_') === 0) {
                    return true;
                }

                if (in_array($key, $setByGrumphp, true)) {
                    return true;
                }

                return false;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
