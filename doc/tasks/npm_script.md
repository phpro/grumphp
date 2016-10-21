# NPM script

The NPM script task will run your configured npm script.
It lives under the `npm_script` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        npm_script:
            script: ~
            triggered_by: []
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

*Default: []*

This option will specify which file extensions will trigger the NPM script.
By default NPM script will be triggered by altering any file.
You can overwrite this option to whatever file you want to use!
