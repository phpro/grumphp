# Parameters

```yaml
# grumphp.yml
parameters:
    bin_dir: ./bin/vendor
    git_dir: .
    ascii:
        failed: resource/grumphp-grumpy.txt
        succeeded: resource/grumphp-happy.txt
```

**bin_dir**

*Default: ./bin/vendor*

This parameter will tell GrumPHP where it can locate external commands like phpcs and phpspec.
It defaults to the default composer bin directory.

**git_dir**

*Default: .*

This parameter will tell GrumPHP in which folder it can find the .git folder.
This parameter is used to create the git hooks at the correct location. It defaults to the working directory.

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


