# NPM script

The NPM script task will run your configured npm script.
It lives under the `npm_script` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        npm_script:
            script: ~
            triggered_by: [js, jsx, coffee, ts, less, sass, scss]
            working_directory: "./"
```

**script**

*Default: null*

This option specifies which NPM script you want to run.
This option is set to null by default.
This means that grumphp will stop immediately.
Note that this script should be used to verify things.
It is also possible to alter code during commit,
but this is surely NOT recommended!


**triggered_by**

*Default: [js, jsx, coffee, ts, less, sass, scss]*

This option will specify which file extensions will trigger the NPM script.
By default NPM script will be triggered by altering any file.
You can overwrite this option to whatever file you want to use!


**working_directory**

*Default: "./"*

This option specifies in which directory the NPM script should be run.
