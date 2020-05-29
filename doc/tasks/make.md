# Make

The Make task will run your automated make tasks.
It lives under the `make` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        make:
            make_file: ~
            task: ~
            triggered_by: [php]
```

**make_file**

*Default: null*

If your Makefile file is located at an exotic location, you can specify your custom make file location with this option.
This option is set to `null` by default.
This means that `Makefile` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Make task you want to run.
This option is set to `null` by default.
This means that make will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the make task.
By default Make will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
