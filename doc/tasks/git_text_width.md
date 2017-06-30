# Git Text Width

The Git Text Width task ensures the number of columns the subject and commit message lines occupy is under the preferred limits.
It lives under the `git_text_width` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        git_text_width:
            max_body_width: 72
            max_subject_width: 60
```

**max_body_width**

*Default: 72*

Preferred limit on the commit message lines.

**max_subject_width**

*Default: 60*

Preferred limit on the subject line.
