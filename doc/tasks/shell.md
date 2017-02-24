# Shell

The Shell task will run your automated shell scripts / commands.
It lives under the `shell` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        shell:
            scripts: []
            triggered_by: [php]
```

**scripts**

*Default: []*

This options specifies the paths to your shell scripts.
You can specify one or more scripts. 
You also can specify one or more shell commands.
All scripts / shell commands need to succeed for the task to complete.

Configuration example:

```yaml
# grumphp.yml
parameters:
    tasks:
        shell:
            scripts:
               - script.sh
               - ["./bin/command", "arg1", "arg2"]
```

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the shell tasks.
By default Shell will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
