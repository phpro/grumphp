# Psalm

Psalm is a static analysis tool for finding errors in PHP applications, built on top of PHP Parser.
It lives under the `psalm` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev vimeo/psalm
```

## Config
```yaml
# grumphp.yml
parameters:
    tasks:
        psalm:
            config: psalm.xml
            ignore_patterns: []
            no_cache: false
            report: ~ 
            threads: 1
            triggered_by: ['php']
            show_info: false
```


**config**

*Default: null*

With this parameter you can specify the path your project's configuration file.


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by psalm. With this option you can skip files like
tests. Leave this option blank to run psalm for every php file/directory specified in your
configuration.


**no_cache**

*Default: false*

With this parameter you can run Psalm without using the cache file.


**report**

*Default: null*

With this path you can specify the path your psalm report file 


**threads**

*Default: null*

This parameter defines on how many threads Psalm's analysis stage is ran.


**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.

**show_info**

*Default: false*

Show non-exception parser findings