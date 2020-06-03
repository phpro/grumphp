# PHPLint

The PHPLint task will check your source files for syntax errors.

***Composer***

```
composer require --dev php-parallel-lint/php-parallel-lint
```

***Config***

```yaml
# grumphp.yml
grumphp:
    tasks:
        phplint:
            exclude: []
            jobs: ~
            short_open_tag: false
            ignore_patterns: []
            triggered_by: ['php', 'phtml', 'php3', 'php4', 'php5']
```
**exclude**

*Default: []*

Any directories to be excluded from linting. You can specify which
directories you wish to exclude, such as the vendor directory.

**jobs**

*Default: null*

The number of jobs you wish to use for parallel processing. If no number
is given, it is left up to parallel-lint itself, which currently
defaults to 10.

**short_open_tag**

*Default: false*

This option can allow PHP short open tags. 

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by PHPLint. Leave this option blank to run PHPLint for every php file.

**trigered_by**

*Default: ['php', 'phtml', 'php3', 'php4', 'php5']*

Any file extensions that you wish to be passed to the linter.
