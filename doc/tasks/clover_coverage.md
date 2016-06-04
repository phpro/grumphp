# Clover Coverage

The Phpunit task will run your unit tests.
It lives under the `clover_coverage` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        clover_coverage:
            config_file: /tmp/clover.xml
            level: 85
```

**clover_file**

*Required*

The location of the clover code coverage XML file.
**level**

*Default: 100*

The minimum code coverage percentage required to pass.
