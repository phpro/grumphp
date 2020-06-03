# Phing

The Phing task will run your automated PHP tasks.

***Composer***

```
composer require --dev phing/phing
```

***Config***

The task lives under the `phing` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phing:
            build_file: ~
            task: ~
            triggered_by: [php]
```

**build_file**

*Default: null*

If your build.xml file is located at an exotic location, you can specify your custom build file location with this option.
This option is set to `null` by default.
This means that `build.xml` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Phing task you want to run.
This option is set to `null` by default.
This means that phing will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the phing task.
By default Phing will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
