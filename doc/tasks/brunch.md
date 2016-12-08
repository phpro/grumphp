# Brunch

The Brunch task will run your automated frontend tasks.
It lives under the `brunch` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        brunch:
            brunch_file: ~
            task: ~
            triggered_by: [js, jsx, coffee, ts, less, sass, scss]
```

**brunch_file**

*Default: null*

If your `brunch-config.js file is located at an exotic location, you can specify your custom gulp file location with this option.
This option is set to `null` by default.
This means that `brunch-config.js` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Brunch task you want to run.
This option is set to `build` by default.
This means that brunch will run the `build` task.
Note that this task should be used to compile your assets. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [js, jsx, coffee, ts, less, sass, scss]*

This option will specify which file extensions will trigger the brunch task.
By default Brunch will be triggered by altering a front-end file. 
You can overwrite this option to whatever file you want to use!
