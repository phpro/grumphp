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
```

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.
