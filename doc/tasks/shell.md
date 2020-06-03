# Shell

The Shell task will run your automated shell scripts / commands.
It lives under the `shell` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        shell:
            scripts: []
            triggered_by: [php]
```

**scripts**

*Default: []*

This options specifies the paths to your shell scripts.
You can specify which executables or shell commands should run.
If you want to run a command, add `-c` as a first argument. This will execute the command instead of trying to open and interpret it.
All scripts / shell commands need to succeed for the task to complete.

Configuration example:

```yaml
# grumphp.yml
grumphp:
    tasks:
        shell:
            scripts:
               - script.sh
               - ["-c", "./bin/command arg1 arg2"]
```

*Note:* When using the `-c` option, the next argument should contain the full executable with all parameters. Be carefull: quotes will be escaped!


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the shell tasks.
By default Shell will be triggered by altering a PHP file. 
You can overwrite this option to whatever file you want to use!
