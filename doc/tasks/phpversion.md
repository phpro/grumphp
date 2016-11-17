# PhpVersion

The Phpversion task will check if your current php version is still supported.
It lives under the `phpversion` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpversion:
            project: 7.0
```

**project**

*Default: null*

Allows to set a higher version than only currently supported version for your project.