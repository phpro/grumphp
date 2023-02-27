# Roave Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.

***Composer***

```
composer require --dev roave/security-advisories:lastest-dev
```
More information about the library can be found on [GitHub](https://github.com/Roave/SecurityAdvisories).

***Config***

The task lives under the `securitychecker_roave` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        securitychecker_roave:
            jsonfile: ./composer.json
            lockfile: ./composer.lock
            run_always: false
```

**jsonfile**

*Default: ./composer.json*

If your `composer.json` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.json` file in the current directory.

**lockfile**

*Default: ./composer.lock*

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.lock` file in the current directory.

**run_always**

*Default: false*

When this option is set to `false`, the task will only run when the `composer.lock` file has changed. If it is set to `true`, the `composer.lock` file will be checked on every commit.
