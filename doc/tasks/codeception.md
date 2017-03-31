# Codeception
The Codeception task will run your full-stack tests.

***Composer***

```
composer require --dev codeception/codeception
```

***Config***

The task lives under the `codeception` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        codeception:
            config_file: ~
            fail_fast: false
            suite: ~
            test: ~
```


**config_file**

*Default: null*

If your `codeception.yml` file is located in an exotic location, you can specify your custom config file location with this option. This option is set to `null` by default. This means that `codeception.yml` is automatically located if it exists in the current directory.

**fail_fast**

*Default: false*

When this option is enabled, Codeception will stop at the first error. This means that it will not run your full test suite when an error occurs.

**suite**

*Default: null*

When this option is specified it will only run tests for the given suite. If left `null` Codeception will run tests for your full test suite.

**test**

*Default: null*

When this option is specified it will only run the given test. If left `null` Codeception will run all tests within the suite.
This option can only be used in combination with a suite.
