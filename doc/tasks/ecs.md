#  Ecs - EasyCodingStandard

Check coding standard in one or more directories.

***Composer***

```
composer require --dev symplify/easycodingstandard
```

***Config***

The task lives under the `ecs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        ecs:
            config: ~
            level: ~
            whitelist_patterns: []
            triggered_by: ['php']
            clear-cache: false
            no-progress-bar: true

```

**config**

*Default: null*

If you want to use a different config file than the default easy-coding-standard.yml, you can specify your custom config file location with this option.


**level**

*Default: null*

If you want to use a different level than the default one, specify it with this option.


**whitelist_patterns**

*Default: []*

If you want to run on particular directories only, specify it with this option.

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger this task.


**clear-cache**

*Default: false*

Clear cache for already checked files.


**no-progress-bar**

*Default: false*

Hide progress bar. Useful e.g. for nicer CI output.

