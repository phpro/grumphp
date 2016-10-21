# Composer script

The Composer script task will run your configured Composer script.
It lives under the `composer_script` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        composer_script:
            script: ~
            triggered_by: []
```

**script**

*Default: null*

This option specifies which Composer script you want to run.
This option is set to null by default.
This means that grumphp will stop immediately.
Note that this script should be used to verify things.
It is also possible to alter code during commit,
but this is surely NOT recommended!


**triggered_by**

*Default: []*

This option will specify which file extensions will trigger the Composer script.
By default Composer script will be triggered by altering any file.
You can overwrite this option to whatever file you want to use!
