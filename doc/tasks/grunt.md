# Grunt

The Grunt task will run your automated frontend tasks.
It lives under the `grunt` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        grunt:
            grunt_file: ~
            task: ~
            triggered_by: [js, jsx, coffee, ts, less, sass, scss]
```

**grunt_file**

*Default: null*

If your Gruntfile.js file is located at an exotic location, you can specify your custom grunt file location with this option.
This option is set to `null` by default.
This means that `Gruntfile.js` is automatically loaded if the file exists in the current directory.


**task**

*Default: null*

This option specifies which Grunt task you want to run.
This option is set to `null` by default.
This means that grunt will run the `default` task.
Note that this task should be used to verify things. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!


**triggered_by**

*Default: [js, jsx, coffee, ts, less, sass, scss]*

This option will specify which file extensions will trigger the grunt task.
By default Grunt will be triggered by altering a front-end file. 
You can overwrite this option to whatever file you want to use!
