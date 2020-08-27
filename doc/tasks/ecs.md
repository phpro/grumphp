#  Ecs - EasyCodingStandard

Check coding standard in one or more directories.

***Composer***

```
composer require --dev symplify/easy-coding-standard
```

***Config***

The task lives under the `ecs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        ecs:
            config: ~
            level: ~
            paths: []
            files_on_pre_commit: false
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


**paths**

*Default: []*

Specify which folders you want to run ecs on.
If you don't set any paths, ECS falls back to [the paths configuration inside your config file](https://github.com/symplify/easy-coding-standard#set-paths).
Be aware: If both the CLI and the config file don't contain any paths, the task will always return true.

**files_on_pre_commit**

*Default: false*

This option makes it possible to use the changed files as paths during pre-commits.
It will use the paths option to make sure only committed files that match the path are validated.

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger this task.


**clear-cache**

*Default: false*

Clear cache for already checked files.


**no-progress-bar**

*Default: false*

Hide progress bar. Useful e.g. for nicer CI output.

