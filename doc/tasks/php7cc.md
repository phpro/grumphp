# Php7cc

The Php7cc task will check PHP 5.3 - 5.6 code compatibility with PHP 7.
It lives under the `php7cc` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        php7cc:
            exclude: ['vendor']
            level: 'error'
            triggered_by: ['php']
```

**exclude**

*Default: [vendor]*

This is a list of directories to be excluded

**level**

*Default: null*

Minimum issue level. There are 3 issue levels: "info", "warning" and "error". "info" is reserved for future use and is the same as "warning".

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
