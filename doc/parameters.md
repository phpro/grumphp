# Parameters

```yaml
# grumphp.yml
grumphp:
    hooks_dir: ~
    hooks_preset: local
    git_hook_variables:
        VAGRANT_HOST_DIR: .
        VAGRANT_PROJECT_DIR: /var/www
        EXEC_GRUMPHP_COMMAND: exec
        ENV: {}
    stop_on_failure: false
    ignore_unstaged_changes: false
    hide_circumvention_tip: false
    process_timeout: 60
    additonal_info: ~
    ascii:
        failed: resource/grumphp-grumpy.txt
        succeeded: resource/grumphp-happy.txt
    parallel:
        enabled: true
        max_workers: 32
    fixer:
        enabled: true
        fix_by_default: false
    environment:
        files: []
        variables: {}
        paths: []
```

**hooks_dir**

*Default: null*

This parameter will tell GrumPHP in which folder it can find the git hooks template folder.
It is used to find the git hooks at a custom location so that you can write your own GIT hooks.
It defaults to null, which means that the default folder `resources/hooks `is used.

**hooks_preset**

*Default: local*

This parameter will tell GrumPHP which hooks preset to use.
Presets are only used when you did NOT specify a custom `hooks_dir`.
GrumPHP comes with following presets:

- `local`: All checks will run on your local computer.
- `vagrant`: All checks will run in your vagrant box (your certainly want to customize `git_hook_variables` default values in this case).

**git_hook_variables**

This parameter will allow you to customize git hooks templates.
After changing any of these variables, you need to run the `git:init` command in order to persist the changes inside your git hooks.
A list of the supported variables: 

-  `VAGRANT_HOST_DIR` : specifies the vagrant location on your host machine relative to the git working folder (_default_ `.`)
-  `VAGRANT_PROJECT_DIR` : specifies the project dir location **inside** the vagrant box (_default_ `/var/www`)
-  `EXEC_GRUMPHP_COMMAND` : specifies the command that will execute the grumphp script (_default_ `exec`)

    It can be a string or an array. If you provide an array, its every element will be escaped by symfony/process.
    If you provide a string, it will be used as-is, without escaping and if it contains special characters you may
    need to escape them.   

    Examples: 
    
    ```yaml
    grumphp:
        git_hook_variables:
            EXEC_GRUMPHP_COMMAND: '/usr/local/bin/php72'
            EXEC_GRUMPHP_COMMAND: 'lando php'
            EXEC_GRUMPHP_COMMAND: 'fin exec php'
            EXEC_GRUMPHP_COMMAND: ['php', '-c /custom/config.ini']
            EXEC_GRUMPHP_COMMAND: ['docker-compose', 'run', '--rm', '--no-deps', 'php']
            EXEC_GRUMPHP_COMMAND: 'docker run --rm -it -v $(pwd):/grumphp -w /grumphp webdevops/php:alpine'
    ```
-  `ENV` : Specify environment variables that will be placed in the git hook file. (_default_ `{}`)

    Examples: 
    
    ```yaml
    grumphp:
        git_hook_variables:
            ENV:
               VAR1: STRING
               VAR2: "'escaped'"
               VAR3: "$(pwd)"
    ```
   
   These environment variables can be overwritten by the `environment` settings inside your `grumphp.yml`.

**stop_on_failure**

*Default: false*

This parameter will tell GrumPHP to stop running tasks when one of the tasks results in an error.
By default GrumPHP will continue running the configured tasks.

**ignore_unstaged_changes**

*Default: false*

By enabling this option, GrumPHP will stash your unstaged changes in git before running the tasks.
This way the tasks will run with the code that is actually committed without the unstaged changes.
Note that during the commit, the unstaged changes will be stored in git stash.
This may mess with your working copy and result in unexpected merge conflicts.

**hide_circumvention_tip**

*Default: false*

Hides the tip describing how to circumvent the Git commit hook and bypass GrumPHP when a task fails.

**process_timeout**

*Default: 60*

GrumPHP uses the Symfony Process component to run external tasks.
The component will trigger a timeout after 60 seconds by default.
If you've got tools that run more then 60 seconds, you can increase this parameter.
It is also possible to disable the timeout by setting the value to `null`.
When receiving a `Symfony\Component\Process\Exception\ProcessTimedOutException` during the execution of GrumPHP, you probably need to increment this setting.

**additional_info**

*Default: null*

This parameter will display additional information at the end of a `success` *or* `error` task.

```yaml
# grumphp.yml
grumphp:
  additional_info: "\nTo get full documentation for the project!\nVisit https://docs.example.com\n"
```

*Example Result:*
```
GrumPHP is sniffing your code!
Running task 1/1: Phpcs... ✔

To get full documentation for the project!
Visit https://docs.example.com

```

**ascii**

*Default: {failed: grumphp-grumpy.txt, succeeded: grumphp-happy.txt}*

This parameter will tell GrumPHP where it can locate ascii images used in pre-commit hook.
Currently there are only three images `failed` and `succeeded`. If path is not specified default image from
`resources/ascii/` folder are used.

You may also specify lists of ascii images, and GrumPHP will choose a random one
from the list.

```yaml
# grumphp.yml
grumphp:
    ascii:
        failed:
            - resource/grumphp-grumpy.txt
            - resource/nopecat.txt
            - resource/failed.txt
        succeeded:
            - resource/grumphp-happy.txt
            - resource/me-gusta.txt
            - resource/succeeded.txt
```

To disable all banners set ascii to `~`:

```yaml
# grumphp.yml
grumphp:
    ascii: ~
```

To disable a specific banner set ascii image path to `~`:

```yaml
# grumphp.yml
grumphp:
    ascii:
        succeeded: ~
```


**parallel**

The parallel section can be used to configure how parallel execution works inside GrumPHP.
You can specify following options:

```
grumphp:
    parallel:
        enabled: true
        max_workers: 32
```

**parallel.enabled**

*Default: true*

You can choose to enable or disable the parallel execution.
If for some reason parallel tasks don't work for you, you can choose to run them in sequence.


**parallel.max_size**

*max_workers: 32*

The maximum amount of workers inside the parallel worker pool.


**fixer**

GrumPHP provides a way of fixing your code. 
However, we won't automatically commit the changes, so that you have the chance to review what has been fixed!
You can configure how fixers work with following config:

```
grumphp:
    fixer:
        enabled: true
        fix_by_default: false
```

**fixer.enabled**

*Default: true*

You can choose to enable or disable built-in fixers.


**fixer.fix_by_default**

*Default: false*

In some contexts, like git commits, it is currently not possible to ask dynamic questions.
Therefor, you can choose what the default answer will be.

**environment**

GrumPHP makes it possible to configure your environment from inside your config file. 
It can load ini files, export bash variables and prepend paths to your `$PATH` variable.

```
grumphp:
    environment
        files: []
        variables: {}
        paths: []
```

**environment.files**

*Default: []*

This parameter can be used to specify a list of ini or .env files that need to be loaded.

Example:

```yaml
grumphp:
  environment:
    files:
        - .env
        - .env.local
```

**environment.variables**

*Default: {}*

Besides loading variables from .env files, you can also specify them directly in your config file.

Example:

```yaml
grumphp:
  environment:
    variables:
        VAR1: "content"
        VAR2: "content"
```

**environment.paths**

*Default: []*

These paths will be prepended in your systems `PATH` variable whilst running GrumPHP.
This makes it possible to e.g. add the project's `phive` tools instead of adding them as dev dependencies in composer.

Example:

```yaml
grumphp:
  environment:
    paths:
        - tools
```
