# Phpspec

The Phpspec task will spec your code with Phpspec.
It lives under the `phpspec` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpspec:
            config_file: ~
            stop_on_failure: false
```

**config_file**

*Default: null*

If your phpspec.yml file is located at an exotic location, you can specify your custom config file location with this option.


**stop_on_failure**

*Default: false*

When this option is enabled, phpspec will stop at the first error. This means that it will not run your full test suite when an error occurs.
