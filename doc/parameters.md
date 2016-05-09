# Parameters

```yaml
# grumphp.yml
parameters:
    bin_dir: ./vendor/bin
    git_dir: .
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
This parameter is used to find the git hooks at a custom location. It defaults to null and the default folder is used.

**hooks_preset**

*Default: local*

This parameter will tell GrumPHP which hooks preset to use. GrumPHP comes with two presets, local and vagrant.

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
