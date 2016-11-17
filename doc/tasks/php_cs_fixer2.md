# PHP-CS-Fixer 2

The PHP-CS-Fixer 2 task will run codestyle checks.
It lives under the `phpcsfixer2` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcsfixer2:
            allow_risky: false
            cache_file: ~
            config: ~
            rules: []
            using_cache: true
            path_mode: ~
            verbose: true
```


**allow_risky**

*Default: false*

The allow_risky option allows you to set whether riskys fixer may run. 
Risky fixer is a fixer, which could change code behaviour. 
By default no risky fixers are run.


**cache_file**

*Default: null*

You can change the location of the cache file by changing this property.
When no cache_file is set, the default file `.php_cs.cache` will be used.


**config**

*Default: null*

By defaut the `.php_cs` wil be used.
You can specify an alternate location for this file by changing this option.


**rules**

*Default: []*

You can limit the amount of rules that are being checked.
The rules option lets you choose the exact fixers to apply.


**using_cache**

*Default: true*

The caching mechanism is enabled by default. 
This will speed up further runs by fixing only files that were modified since the last run. 
The tool will fix all files if the tool version has changed or the list of fixers has changed. 
Cache is supported only for tool downloaded as phar file or installed via composer.


**path_mode**

*Default: null*

Specify path mode (can be override or intersection).


**verbose**

*Default: true*

Show applied fixers.
