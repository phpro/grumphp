# Local Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.

***Binary***

Download the latest binary from [fabpot/local-php-security-checker ](https://github.com/fabpot/local-php-security-checker/releases) and make sure it is part of your PATH or place it in one of the directories defined by environment.paths in your grumphp.yml file.

***Config***

The task lives under the `securitychecker_local` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        securitychecker_local:
            lockfile: ./composer.lock
            format: ~
            run_always: false
```

**lockfile**

*Default: ./composer.lock*

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.lock` file in the current directory.

**format**

*Default: null*

You can choose the format of the output. The available options are `ansi`, `json`, `markdown` and `yaml`. By default, grumphp will use the format `ansi`.

**run_always**

*Default: false*

When this option is set to `false`, the task will only run when the `composer.lock` file has changed. If it is set to `true`, the `composer.lock` file will be checked on every commit.
