# Git commit message

The git comit message can be used in combination with the git hook `git:commit-msg`.
It can be used to enforce patterns in a commit message.
For example: if you are working with JIRA, it is possible to add a pattern for the JIRA issue number.

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

**multiline**

*Default:true*

Mark the matchers as multiline.
