# TwigLint

The TwigLint task will lint all your twig files.
It lives under the `twiglint` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        twiglint:
            triggered_by: ['twig']
```

**triggered_by**

*Default: [twig]*

This is a list of extensions to be sniffed.
