# Git Blacklist

The Git Blacklist task will test your changes for blacklisted keywords, such as `die(`, `var_dump(` etc.
It lives under the `git_blacklist` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        git_blacklist:
            keywords:
                - "die("
                - "var_dump("
                - "exit;"
            triggered_by: [php]
```

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the git blacklist task.
By default git blacklist will be triggered by altering a php file. 
You can overwrite this option to whatever filetype you want to validate!
