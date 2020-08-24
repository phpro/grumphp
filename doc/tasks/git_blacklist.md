# Git Blacklist

The Git Blacklist task will test your changes for blacklisted keywords, such as `die(`, `var_dump(` etc.
It lives under the `git_blacklist` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        git_blacklist:
            keywords:
                - "die("
                - "var_dump("
                - "exit;"
            whitelist_patterns: []
            triggered_by: ['php']
            regexp_type: G
            match_word: false
```

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.
Please note that reserved regex characters require proper escaping i.e. `"_GET\\["` in the yaml.

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

**match_word**

*Default: false*

This option allows you to choose how the keywords is found.

For instance let's say you have a keyword looking like `"dd("` by default this task would also find any
text before or after the keyword meaning this: `function add($someTask)` would still be considered invalid.
This configuration option allows you to get around that issue.
