# Composer Normalize

If you are using `composer`, you have probably modified the file `composer.json` at least once to keep things nice
and tidy.

This task is a wrapper around a composer plugin for tidying up the file `composer.json`.

The default configuration looks like:

```yaml
# grumphp.yml
parameters:
    tasks:
        composer_normalize:
            indent_size: ~
            indent_style: ~
            no_update_lock: true
            verbose: false
```

**indent_size**

*Default: null*

Indent size (an integer greater than 0); must be used with the `indent_style` option

**indent_style**

*Default: null*

Indent style (one of "space", "tab"); must be used with the `indent_size` option

**no_update_lock**

*Default: true*

If `false`, do not update lock file if it exists.

**verbose**

*Default: false*

Set this to true if you want to see the diff.
