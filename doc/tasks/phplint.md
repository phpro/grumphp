# PHPLint

The PHPLint task will check your source files for syntax errors.

```yaml
# grumphp.yml
parameters:
    tasks:
        phplint:
            to_check: [ '.' ]
            exclude: []
            jobs: ~
```

**to_check**

*Default: project root*

The directories to check. By default, it checks the entire repository,
but you can fine-tune this as needed.

**exclude**

*Default: array()*

Any directories to be excluded from linting. You can specify which
directories you wish to exclude, such as the vendor directory.

**jobs**

*Default: null*

The number of jobs you wish to use for parallel processing. If no number
is given, it is left up to parallel-lint itself, which currently
defaults to 10.
