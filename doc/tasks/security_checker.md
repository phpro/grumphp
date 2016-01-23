# Security Checker

The Security Checker will check your `composer.lock` file for known security vulnerabilities.
It lives under the `securitychecker` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        securitychecker:
            lockfile: ~
            format: ~
            end_point: ~
            timeout: ~
```

**lockfile**

*Default: null*

If your `composer.lock` file is located in an exotic location, you can specify the location with this option. This option is set to `null` by default. This means that the command will try to load a `composer.lock` file in the current directory.

**format**

*Default: null*

You can choose the format of the output. The available options are `text`, `json` and `simple`. By default, grumphp will use the format `text`.

**end_point**

*Default: null*

You can use a different end point for the security checks. Grumphp will use the default end point which is [https://security.sensiolabs.org/check_lock](https://security.sensiolabs.org/check_lock).

**timeout**

*Default: null*

You can change the timeout value for the command. By default this value is `20`.
