# YamlLint

The YamlLint task will lint all your yaml files.
It lives under the `yamllint` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        yamllint:
            whitelist_patterns: []
            ignore_patterns: []
            object_support: false
            exception_on_invalid_type: false
            parse_constant: false
            parse_custom_tags: false
```

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For example: whitelist files in `src/FolderA/` and `src/FolderB/` you can use 
```yml
whitelist_patterns:
  - /^src\/FolderA\/(.*)/
  - /^src\/FolderB\/(.*)/
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


**parse_constant**

*Default: false*

By enabling this option, constants defined by the special `!php/const:` syntax is parsed and validated.
When this option is not set, the constant syntax will trigger an error


**parse_custom_tags**

*Default: false*

By enabling this option, custom tags in the yaml file will be parsed and validated (E.G `!my_tag { foo: bar }`).
When this option is not set, using custom tags will trigger an error
