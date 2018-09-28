# Git commit message

The Git commit message task ensures commit messages match the specified patterns.
For example: if you are working with JIRA, it is possible to add a pattern for the JIRA issue number.

```yaml
# grumphp.yml
parameters:
    tasks:
        git_commit_message:
            allow_empty_message: false
            enforce_capitalized_subject: true
            enforce_no_subject_punctuations: false
            enforce_no_subject_trailing_period: true
            enforce_single_lined_subject: true
            type_scope_conventions: []
            max_body_width: 72
            max_subject_width: 60
            matchers:
                Must contain JIRA issue number: /JIRA-\d+/
            case_insensitive: true
            multiline: true
            additional_modifiers: ''
```

**allow_empty_message**

*Default: false*

Controls whether or not empty commit messages are allowed.

**enforce_capitalized_subject**

*Default: true*

Ensures that the commit message subject line starts with a capital letter.

**enforce_no_subject_punctuations**

*Default: false*

Ensures that the commit message subject line doesn't have any punctuations characters.

**enforce_no_subject_trailing_period**

*Default: true*

Ensures that the commit message subject line doesn't have a trailing period.

**enforce_single_lined_subject**

*Default: true*

Ensures that the commit message subject line is followed by a blank line.

**max_body_width**

*Default: 72*

Preferred limit on the commit message body lines. Set to 0 to disable the check.

**max_subject_width**

*Default: 60*

Preferred limit on the commit message subject line. Set to 0 to disable the check.

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


**additional_modifiers**

*Default: ''*

Add one or multiple additional modifiers like:

```yaml
additional_modifiers: 'u'

# or

additional_modifiers: 'xu'
```

**type_scope_conventions**

*Default: []*

Enable a commonly used convention for the subject line.

Format is as follows:
```
type[(scope)]: subject
```
*The scope is optional*

Good examples:
```
fix: Security issue with password hashing
fix(Password): Security issue with password hashing  
```

**types**

*Default: []*

*To be used with `type_scope_conventions`*

Specify a list of acceptable types. Default allows ***all*** types.

Add one or multiple types like:
```yaml
type_scope_conventions:
  - types:
    - build
    - ci
    - chore
    - docs
    - feat
    - fix
    - perf
    - refactor
    - revert
    - style
    - test
  - scopes: []
```

**scopes**

*Default: []*

*To be used with `type_scope_conventions`*

Specify a list of acceptable scopes. Default allows ***all*** scopes.

Add one or multiple scopes like:
```yaml
type_scope_conventions:
  - types: []
  - scopes:
      - api
      - index
      - user
      - language
      - browser
      - environment
```

