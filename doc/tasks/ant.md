# Ant

The Ant task will run your automated Ant tasks.
It lives under the `ant` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        ant:
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

This option specifies which Ant task you want to run.
This option is set to `null` by default.
This means that ant will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the ant task.
By default Ant will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
