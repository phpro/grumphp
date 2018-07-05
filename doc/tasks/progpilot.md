# Progpilot

The Progpilot task will run your automated PHP tasks.

***Composer***

```
composer config minimum-stability dev
composer require --dev designsecurity/progpilot:dev-master
```

***Config***

The task lives under the `progpilot` namespace and has following configurable parameters.

```yaml
# grumphp.yml
parameters:
    tasks:
        progpilot:
            config_file: .progpilot/configuration.yml
            triggered_by: [php]
```

**config_file**

*Default: configuration.yml*

You can specify your custom configuration file location with this option.
By default `.progpilot/configuration.yml` is automatically loaded if the file exists in the current directory.
If not or yml file format is invalid default configuration of progpilot will be used.


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the Progpilot task.
By default Progpilot will be triggered by altering a PHP file.
 
