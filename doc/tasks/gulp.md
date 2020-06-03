# Gulp

The Gulp task will run your automated frontend tasks.
It lives under the `gulp` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        gulp:
            gulp_file: ~
            task: ~
            triggered_by: [js, jsx, coffee, ts, less, sass, scss]
```

**gulp_file**

*Default: null*

If your Gulpfile.js file is located at an exotic location, you can specify your custom gulp file location with this option.
This option is set to `null` by default.
This means that `gulpfile.js` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Gulp task you want to run.
This option is set to `null` by default.
This means that gulp will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [js, jsx, coffee, ts, less, sass, scss]*

This option will specify which file extensions will trigger the gulp task.
By default Gulp will be triggered by altering a front-end file. 
You can overwrite this option to whatever file you want to use!
