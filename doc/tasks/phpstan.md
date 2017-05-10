# PHPStan

The PHPStan task focuses on finding errors in your code without actually running it. It catches whole classes of bugs even before you write tests for the code.
It lives under the `phpstan` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev phpstan/phpstan
```

## Config
```yaml
# grumphp.yml
parameters:
    tasks:
        phpstan:
            autoload_file: ~
            configuration: ~
            level: 0
            triggered_by: ['php']
```

**autoload_file**

*Default: ~*

With this parameter you can specify the path your project's additional autoload file path.

**configuration**

*Default: ~*

With this parameter you can specify the path your project's configuration file.

**level**

*Default: 0*

With this parameter you can set the level of rule options - the higher the stricter.

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
