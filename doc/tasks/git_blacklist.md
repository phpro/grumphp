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
            whitelist_patterns: []
            triggered_by: ['php']
            regexp_type: G
```

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For example: whitelist files in `src/FolderA/` and `src/FolderB/` you can use 
```yml
whitelist_patterns:
  - /^src\/FolderA\/(.*)/
  - /^src\/FolderB\/(.*)/
```

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the git blacklist task.
By default git blacklist will be triggered by altering a php file. 
You can overwrite this option to whatever filetype you want to validate!

**regexp_type**

*Default: G*

This option allows you to choose the type of regexp you want to use for patterns (can be G for POSIX basic, E for POSIX extended or P for Perl Compatible).
