#  Behat

The Behat task will run your Behat tests.

***Composer***

```
composer require --dev behat/behat
```

***Config***

The task lives under the `behat` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        behat:
            config: ~
            format: ~
            stop_on_failure: false
```

**config**

*Default: null*

If you want to use a different config file than the default behat.yml, you can specify your custom config file location with this option.


**format**

*Default: null*

If you want to use a different formatter than the default one, specify it with this option.


**suite**

*Default: null*

If you want to run a particular suite only, specify it with this option.


**stop_on_failure**

*Default: false*

When this option is enabled, behat will stop at the first error. This means that it will not run your full test suite when an error occurs.
