# Phpspec

Kahlan is a full-featured Unit & BDD test framework a la RSpec/JSpec which uses a describe-it syntax and moves testing in PHP one step forward.
It lives under the `kahlan` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        kahlan:
          config: ~
          src: ~
          spec: ~
          pattern: ~
          reporter: ~
          coverage: ~
          clover: ~
          istanbul: ~
          lcov: ~
          ff: ~
          'no-colors': ~
          'no-header': ~
          include: ~
          exclude: ~
          persistent: ~
          cc: ~
          autoclear: ~
```

Every options available are documented on [kahlan](https://kahlan.github.io/docs/cli-options.html)