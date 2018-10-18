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
            force_patterns: []
            ignore_patterns: []
            triggered_by: ['php']
```

**autoload_file**

*Default: null*

With this parameter you can specify the path your project's additional autoload file path.

**configuration**

*Default: null*

With this parameter you can specify the path your project's configuration file.

**level**

*Default: 0*

With this parameter you can set the level of rule options - the higher the stricter.

**force_patterns**

*Default: []*

This is a list of patterns that will be forced for analysis even when the file or path is ignored.

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpstan. With this option you can skip files like tests. Leave this option blank to run phpstan for every php file.

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
