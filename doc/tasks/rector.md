# Rector

Rector is a tool to instantly upgrade and automatically refactor your PHP 5.3+ code.
It lives under the `rector` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev rectorphp/rector
```

## Config
```yaml
# grumphp.yml
grumphp:
    tasks:
        rector:
            config: rector.php
            triggered_by: ['php']
            ignore_patterns: []
            clear_cache: true
            no_progress_bar: true
            no_diffs: false
```

**config**

*Default: rector.php*

With this parameter you can specify the path your project's configuration file.

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by Rector. With this option you can skip files like
tests. Leave this option blank to run Rector for every php file/directory specified in your
configuration.


**clear_cache**

*Default: true*

With this parameter you can run Rector without using the cache.

**no_progress_bar**

*Default: true*

With this parameter you can run Rector without showing the progress bar.

**no_diffs**

*Default: false*

With this parameter you can run Rector without showing file diffs.

