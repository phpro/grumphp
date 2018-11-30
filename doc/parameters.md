# Parameters

```yaml
# grumphp.yml
parameters:
    bin_dir: ./vendor/bin
    git_dir: .
    hooks_dir: ~
    hooks_preset: local
    stop_on_failure: false
    ignore_unstaged_changes: false
    hide_circumvention_tip: false
    process_async_limit: 10
    process_async_wait: 1000
    process_timeout: 60
    additonal_info: ~
    ascii:
        failed: resource/grumphp-grumpy.txt
        succeeded: resource/grumphp-happy.txt
```

**bin_dir**

*Default: ./vendor/bin*

This parameter will tell GrumPHP where it can locate external commands like phpcs and phpspec.
It defaults to the default composer bin directory.

**git_dir**

*Default: .*

This parameter will tell GrumPHP in which folder it can find the .git folder.
This parameter is used to create the git hooks at the correct location. It defaults to the working directory.

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
- `vagrant`: All checks will run in your vagrant box.


*Note:*
When using the vagrant preset, you are required to set the vagrant SSH home folder to your working directory.
This can be done by altering the `.bashrc` or `.zshrc` inside your vagrant box:

```sh
echo 'cd /remote/path/to/your/project' >> ~/.bashrc
```

You can also add the `.bashrc` or `.zshrc` to your vagrant provision script.
This way the home directory will be set for all the people who are using your vagrant box.

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

**process_async_limit**

*Default: 10*

This parameter controls how many asynchronous processes GrumPHP will run simultaneously. Please note
that not all external tasks uses asynchronous processes, nor would they necessarily benefit from using it.

**process_async_wait**

*Default: 1000*

This parameter controls how long GrumPHP will wait (in microseconds) before checking the status of all asynchronous processes.

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
parameters:
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
Currently there are only two images `failed` and `succeeded`. If path is not specified default image from
`resources/ascii/` folder are used.

You may also specify lists of ascii images, and GrumPHP will choose a random one
from the list.

```yaml
# grumphp.yml
parameters:
    ascii:
        failed:
            - resource/grumphp-grumpy1.txt
            - resource/grumphp-grumpy2.txt
        succeeded:
            - resource/grumphp-happy1.txt
            - resource/grumphp-happy2.txt
            - resource/grumphp-happy3.txt
```

To disable all banners set ascii to `~`:

```yaml
# grumphp.yml
parameters:
    ascii: ~
```

To disable a specific banner set ascii image path to `~`:

```yaml
# grumphp.yml
parameters:
    ascii:
        succeeded: ~
```
