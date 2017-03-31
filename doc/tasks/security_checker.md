# Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.

***Composer***

```
composer require --dev sensiolabs/security-checker
```

***Config***

The task lives under the `securitychecker` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        securitychecker:
            lockfile: ./composer.lock
            format: ~
            end_point: ~
            timeout: ~
            run_always: false
```

**lockfile**

*Default: ./composer.lock*

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. By default, the task will try to load a `composer.lock` file in the current directory.

**format**

*Default: null*

You can choose the format of the output. The available options are `text`, `json` and `simple`. By default, grumphp will use the format `text`.

**end_point**

*Default: null*

You can use a different end point for the security checks. Grumphp will use the default end point which is [https://security.sensiolabs.org/check_lock](https://security.sensiolabs.org/check_lock).

**timeout**

*Default: null*

You can change the timeout value for the command. By default this value is `20`.

**run_always**

*Default: false*

When this option is set to `false`, the task will only run when the `composer.lock` file has changed. If it is set to `true`, the `composer.lock` file will be checked on every commit.
