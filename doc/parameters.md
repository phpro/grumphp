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


*Note:* When using the vagrant preset, make sure the working directory of the vagrant shell is set as vagrant_dir

**vagrant_dir**

*Default: .*

This parameter will tell GrumPHP in which folder in the vagrant environment the project root is.
This is where the grumPHP command will be executed. 

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

**ascii**

*Default: {failed: grumphp-grumpy.txt, succeeded: grumphp-happy.txt}*

This parameter will tell GrumPHP where it can locate ascii images used in pre-commit hook.
Currently there are only two images `failed` and `succeeded`. If path is not specified default image from
`resources/ascii/` folder are used.

To disable banner set ascii images path to `~`:

```yaml
# grumphp.yml
parameters:
    ascii:
        succeeded: ~
```
