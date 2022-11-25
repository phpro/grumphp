# Deptrac

Follow the [installation instructions](https://qossmic.github.io/deptrac/#installation) to add deptrac to your 
project.

The Deptrac task will check for dependencies between the software layers of your project. It lives under the `deptrac` 
namespace and has following configurable parameters:


```yaml
# grumphp.yml
grumphp:
    tasks:
        deptrac:
            cache_file: ~
            depfile: ~
            formatter: ~
            output: ~
```

**cache_file**

*Default: null*

Set location where cache file will be stored. Example: `/var/www/src/.deptrac.cache`

**depfile**

*Default: null*

Set path to deptrac configuration file. Example: `/var/www/src/deptrac.yaml`

**formatter**

*Default: null*

Enable formatter with this option, e.g. `console`, `github-actions`, `graphviz-display`, `graphviz-image`, `graphviz-dot`, `graphviz-html`, `junit`, `table`, `xml`, `baseline`, `json`.

**output**

*Default: null*

Set output file path for formatter (if applicable).
