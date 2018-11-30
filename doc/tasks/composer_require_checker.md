# Composer Require Checker

The Composer Require Checker task analyzes composer dependencies and verifies that no unknown symbols are used in the
code. This will prevent you from using "soft" dependencies that are not defined within your composer.json.
It lives under the `composer_require_checker` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev maglnet/composer-require-checker
```

## Config
```yaml
# grumphp.yml
parameters:
    tasks:
        composer_require_checker:
            composer_file: 'composer.json'
            config_file: ~
            ignore_parse_errors: false
            triggered_by: ['composer.json', 'composer.lock', '*.php']
```

**composer_file**

*Default: null*

The composer.json of your code base that should be checked.

**config_file**

*Default: null*

Composer Require Checker is configured to whitelist some symbols by default. You can now override this configuration
with your own and tell GrumPHP to use that configuration file instead.

**ignore_parse_errors**

*Default: false*

This will cause Composer Require Checker to ignore errors when files cannot be parsed, otherwise errors will be thrown.

This option is only available in version 0.2.0 of `maglnet/composer-require-checker` and above.

**triggered_by**

*Default: ['composer.json', 'composer.lock', '\*.php']*

This is a list of file names that should trigger this task.
