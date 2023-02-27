# Enlightn Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.

***Composer***

```
composer require --dev enlightn/security-checker
```

***Config***

The task lives under the `securitychecker_enlightn` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        securitychecker_enlightn:
            lockfile: ./composer.lock
            run_always: false
```

**lockfile**

*Default: ./composer.lock*

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.lock` file in the current directory.

**run_always**

*Default: false*

When this option is set to `false`, the task will only run when the `composer.lock` file has changed. If it is set to `true`, the `composer.lock` file will be checked on every commit.
