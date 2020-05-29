# Clover Coverage

The Clover Coverage task will run your unit tests.

Note that to make sure that there is always a clover file available, you might need to
set `always_execute` to `true` in the `phpunit` task configuration.

It lives under the `clover_coverage` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        clover_coverage:
            clover_file: /tmp/clover.xml
            level: 100
```

**clover_file**

*Required*

The location of the clover code coverage XML file.

**level**

*Default: 100*

The minimum code coverage percentage required to pass.
