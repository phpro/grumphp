# Pest

The Pest task will run your unit tests.

***Composer***

```
composer require --dev pestphp/pest
```

***Config***

The task lives under the `pest` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        pest:
            config_file: ~
            always_execute: false
```

**config_file**

*Default: null*

If your phpunit.xml file is located at an exotic location, you can specify your custom config file location with this option.
This option is set to `null` by default.
This means that `phpunit.xml` or `phpunit.xml.dist` are automatically loaded if one of them exist in the current directory.

**always_execute**

*Default: false*

Always run the whole test suite, even if no PHP files were changed.
