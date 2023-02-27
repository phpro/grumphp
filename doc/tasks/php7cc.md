# Php7cc

The Php7cc task will check PHP 5.3 - 5.6 code compatibility with PHP 7.

***Composer***

```
composer require --dev sstalle/php7cc
```

***Config***

The task lives under the `php7cc` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        php7cc:
            exclude: []
            level: ~
            triggered_by: ['php']
```

**exclude**

*Default: []*

This is a list of directories to be excluded

**level**

*Default: null*

Minimum issue level. There are 3 issue levels: "info", "warning" and "error". "info" is reserved for future use and is the same as "warning".

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.


## Known issues

- Since this task is using an old version of phpparser, it currently cannot be used in combination with the `phpparser` task. 
[Click here for more information](https://github.com/sstalle/php7cc/issues/79)
