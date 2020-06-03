# Phpspec

The Phpspec task will spec your code with Phpspec.

***Composer***

```
composer require --dev phpspec/phpspec
```

***Config***

The task lives under the `phpspec` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpspec:
            config_file: ~
            format: ~
            stop_on_failure: false
            verbose: false
```

**config_file**

*Default: null*

If your phpspec.yml file is located at an exotic location, you can specify your custom config file location with this option.


**format**

*Default: null*

You can overwrite the default `progress` format or the one specified in the `phpspec.yml` config file by configuring this option.

[A list of all formatters](http://www.phpspec.net/en/stable/cookbook/configuration.html#formatter)


**stop_on_failure**

*Default: false*

When this option is enabled, phpspec will stop at the first error. This means that it will not run your full test suite when an error occurs.


**verbose**

*Default: false*

When this option is enabled, phpspec will display a verbose error message about the failed example. This way, it is easier to debug what went wrong in the specs.
