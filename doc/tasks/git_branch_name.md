# Git commit message

The Git branch name task ensures that the current branch name matches the specified patterns.  
For example: if you are working with JIRA, it is possible to add a pattern for the JIRA issue number.

```yaml
# grumphp.yml
parameters:
    tasks:
        git_branch_name:
            matchers:
                Must contain JIRA issue number: /JIRA-\d+/
            case_insensitive: true
            additional_modifiers: ''
```

**matchers**

*Default: []*

Use this parameter to specify one or multiple patterns. The value can be in regex or glob style.
Here are some example matchers:

- /JIRA-([0-9]*)/
- pre-fix*
- *suffix
- ...

**case_insensitive**

*Default: true*

Mark the matchers as case sensitive.

**additional_modifiers**

*Default: ''*

Add one or multiple additional modifiers like:

```yaml
additional_modifiers: 'u'

# or

additional_modifiers: 'xu'
```
