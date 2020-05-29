# Robo

The Robo task will run your automated PHP tasks.

***Composer***

```
composer require --dev consolidation/robo
```

***Config***

The task lives under the `robo` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        robo:
            load_from: ~
            task: ~
            triggered_by: [php]
```

**load_from**

*Default: null*

If your Robofile.php file is located at an exotic location, you can specify the path to your custom location with this option.
This option is set to `null` by default.
This means that `Robofile.php` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Robo task you want to run.
This option is set to `null` by default.
This means that robo will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the robo task.
By default Robo will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
