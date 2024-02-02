# Composer Audit Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.

***Config***

The task lives under the `securitychecker_composeraudit` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        securitychecker_composeraudit:
            format: null
            locked: true
            no_dev: false
            run_always: false
            working_dir: null
```

**format**

*Default: null*

You can choose the format of the output. The available options are `table`, `plain`, `json` and `summary`. By default, grumphp will use the format `table`.

**locked**

*Default: true*

Audit packages from the lock file, regardless of what is currently in vendor dir.

**no_dev**

*Default: false*

When this option is set to `true`, the task will skip packages under `require-dev`.

**run_always**

*Default: false*

When this option is set to `false`, the task will only run when the `composer.lock` file has changed. If it is set to `true`, the `composer.lock` file will be checked on every commit.

**working_dir**

*Default: null

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.lock` file in the current directory.