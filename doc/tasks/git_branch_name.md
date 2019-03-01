# Git branch name

The Git branch name task ensures that the current branch name matches the specified patterns.
For this task to succeed, **all** whitelist patterns and **none** of the blacklist patterns have to
match the commit message. For example: if you are working with JIRA, it is possible to add a
pattern for the JIRA issue number.
 
```yaml
# grumphp.yml
parameters:
    tasks:
        git_branch_name:
            whitelist:
                - "/JIRA-\d+/"
            blacklist:
                - "develop"
                - "master"
            additional_modifiers: ''
            allow_detached_head: true
```


**whitelist**

*Default: []*

Use this parameter to specify one or multiple patterns. The value can be in regex or glob style.
Here are some example matchers:

- /JIRA-([0-9]*)/
- pre-fix*
- *suffix

**blacklist**

*Default: []*

Use this parameter to specify one or multiple patterns. The value can be in regex or glob style.
Here are some example matchers:

- /JIRA-([0-9]*)/
- pre-fix*
- *suffix


**additional_modifiers**

*Default: ''*

Add one or multiple additional modifiers like:

```yaml
additional_modifiers: 'u'

# or

additional_modifiers: 'xu'
```


**allow_detached_head**

*Default: true*

Set this to `false` if you wish the task to fail when ran on a detached HEAD. If set to `true` the
task will always pass.
