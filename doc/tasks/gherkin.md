# Gherkin

The Gherkin task will lint your Gherkin feature files.
It lives under the `gherkin` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        gherkin:
            directory: 'features'
            align: ~
```

**directory**

*Default: 'features'*

This option will specify the location of your Gherkin feature files.
By default the Behat prefered `features` folder is chosen.

**align**

*Default: null*

This option will specify the alignment of your file.
Possible values can be `left` or `right`.
