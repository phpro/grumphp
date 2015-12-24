# YamlLint

The YamlLint task will lint all your yaml files.
It lives under the `yamllint` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        yamllint:
            ignore_patterns: []
            object_support: false
            exception_on_invalid_type: false
```

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by the linter. 
With this option you can skip files like test fixtures. Leave this option blank to run the linter for every yaml file.


**object_support**

*Default: false*

This option indicates if the Yaml parser supports serialized PHP objects.


**exception_on_invalid_type**

*Default: false*

By enabling this option, the types of the yaml values are validated. 
When the value has an incorrect type, a lint error will be triggered.
