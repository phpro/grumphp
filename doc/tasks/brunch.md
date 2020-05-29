# Brunch

The Brunch task will run your automated frontend tasks.
It lives under the `brunch` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        brunch:
            task: build
            env: production
            jobs: 4
            debug: false
            triggered_by: [js, jsx, coffee, ts, less, sass, scss]
```

**task**

*Default: build*

This option specifies which Brunch task you want to run.
This option is set to `build` by default.
This means that brunch will run the `build` task.
Note that this task should be used to compile your assets. 
It is also possible to alter code during commit, but this is surely **NOT** recommended!

**env**

*Default: production*

This option specifies in which format you want to compile your assets.
E.g: `--env production`. You can specify the env you set up in your brunch config file.

**jobs**

*Default: 4*

This option enables experimental multi-process support. May improve compilation speed of large projects. Try different WORKERS amount to see which one works best for your system.

**debug**

*Default: false*

It enables verbose debug output.

**triggered_by**

*Default: [js, jsx, coffee, ts, less, sass, scss]*

This option will specify which file extensions will trigger the brunch task.
By default Brunch will be triggered by altering a front-end file. 
You can overwrite this option to whatever file you want to use!
