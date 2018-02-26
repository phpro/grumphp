# PHP-CS-Fixer 2

The PHP-CS-Fixer 2 task will run codestyle checks.

***Composer***

```
composer require --dev friendsofphp/php-cs-fixer
```

***Config***

The task lives under the `phpcsfixer2` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcsfixer2:
            allow_risky: ~
            cache_file: ~
            config: ~
            rules: []
            using_cache: ~
            config_contains_finder: true
            verbose: true
            diff: false
            triggered_by: ['php']
```


**allow_risky**

*Default: null*

The allow_risky option allows you to set whether risky rules may run.
Risky rule is a rule, which could change code behaviour.
If not set, the default value is taken from config file (if it exists). By default no risky rules are run.

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

Rules may be specified as either a list or map. A list of rules may only turn certain rules on or off whereas
a map may also configure rules where applicable.

In the following list-style example, PSR-2 rules are enabled but the `line_ending` rule is removed from the
set, while the `array_syntax` rule is added.

```yaml
rules:
  - '@@PSR2'
  - -line_ending
  - array_syntax
```

The following map-style example is the same as the previous, except we take advantage of rule configuration
to change the `array_syntax` validation mode to short array syntax (`[]`) instead of the default long syntax
(`array()`).

```yaml
rules:
  '@PSR2': true
  line_ending: false
  array_syntax:
    syntax: short
```

Note that rule sets, beginning with the *at* symbol (`@`), must be escaped by being quoted and doubled due to
limitations of the parser. However, when appearing in the *key* position as in the previous example, doubling
is incorrect.

**using_cache**

*Default: null*

By using using_cache option you can set if the caching mechanism should be used.
If not set, the default value is taken from config file (if it exists). The caching mechanism is enabled by default.
This will speed up further runs by fixing only files that were modified since the last run.
The tool will fix all files if the tool version has changed or the list of fixers has changed.
Cache is supported only for tool downloaded as phar file or installed via composer.


**config_contains_finder**

*Default: true*

Intersection mode can only be used when you have a configuration file which contains a Finder.
This mode works best since only files that are being commit and are in your configuration will be checked.
When there is no Finder in your configuration, you'll have set this parameter to false. 
Otherwise php-cs-fixer will crash the execution.

**verbose**

*Default: true*

Show applied fixers.

**diff**

*Default: false*

Show the full diff that will be applied.


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the phpcsfixer2 task.

