# JsonLint

The JsonLint task will lint all your json files.
It lives under the `jsonlint` namespace and has following configurable parameters:

```json
# grumphp.yml
parameters:
    tasks:
        jsonlint:
            ignore_patterns: []
            detect_key_conflicts: false
```

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by the linter. 
With this option you can skip files like test fixtures. Leave this option blank to run the linter for every json file.


**detect_key_conflicts**

*Default: false*

This option will throw exceptions when duplicate keys are detected in the json file.
