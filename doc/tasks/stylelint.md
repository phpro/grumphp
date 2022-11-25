# Stylelint ![fixer](https://img.shields.io/badge/-fixer-informational)

[Stylelint](https://stylelint.io/) is a modern linter that helps you avoid errors and enforce conventions in your styles.

## NPM
If you'd like to install it globally:
```bash
npm install -g stylelint
```

If you'd like install it as a dev dependency of your project:
```bash
npm install --save-dev stylelint
```

Done. See the Stylelint [Getting Started](https://stylelint.io/user-guide/get-started) guide for more info.

## Config
It lives under the `stylelint` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        stylelint:
            bin: node_modules/.bin/stylelint
            triggered_by: [css, scss, sass, less, sss]
            whitelist_patterns:
                - /^resources\/css\/(.*)/
            config: ~
            config_basedir: ~
            ignore_path: ~
            ignore_pattern: ~
            syntax: ~
            custom_syntax: ~
            ignore_disables: ~
            disable_default_ignores: ~
            cache: ~
            cache_location: ~
            formatter: ~
            custom_formatter: ~
            quiet: ~
            color: ~
            report_needless_disables: ~
            report_invalid_scope_disables: ~
            report_descriptionless_disables: ~
            max_warnings: ~
            output_file: ~
```

**bin**

*Default: null*

The path to your stylelint bin executable. Not necessary if stylelint is in your $PATH. Can be used to specify path to project's stylelint over globally installed stylelint.

> **Note:** It is possible to add `node_modules/.bin` to the grumphp paths in order for it to be automatically discovered as well:
> 
> ```yaml
> grumphp:
>     environment:
>         paths:
>             - node_modules/.bin
> ```

**triggered_by**

*Default: [css, scss, sass, less, sss]*

This is a list of extensions which will trigger the Sylelint task.


**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can specify the folders containing javascript files and thus skip folders like /tests/ or the /vendor/ directory. This option is used in conjunction with the parameter `triggered_by`.
For example: to whitelist files in `resources/css/` (Laravel's CSS directory) and `assets/css/` (Symfony's CSS directory) you can use:
```yml
whitelist_patterns:
  - /^resources\/css\/(.*)/
  - /^assets\/css\/(.*)/
```

**config**

*Default: null*

Path to a JSON, YAML, or JS file that contains your configuration object. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--config)).

**config_basedir**

*Default: null*

Absolute path to the directory that relative paths defining "extends" and "plugins" are _relative_ to. Only necessary if these values are relative paths. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--config-basedir)).

**ignore_path**

*Default: null*

A path to a file containing patterns describing files to ignore. The path can be absolute or relative to `process.cwd()`. By default, stylelint looks for `.stylelintignore` in `process.cwd()`. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--ignore-path--i)).

**ignore_pattern**

*Default: null*

Pattern of files to ignore (in addition to those in `.stylelintignore`). ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--ignore-pattern---ip)).

**syntax**

*Default: null*

Specify a syntax. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--syntax--s)).

**custom_syntax**

*Default: null*

Specify a custom syntax to use on your code. Use this option if you want to force a specific syntax that's not already built into stylelint. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--custom-syntax)).

**ignore_disables**

*Default: null*

Ignore `styleline-disable` (e.g. `/* stylelint-disable block-no-empty */`) comments. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--ignore-disables---id)).

**disable_default_ignores**

*Default: null*

Disable the default ignores. stylelint will not automatically ignore the contents of `node_modules`. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--disable-default-ignores---di)).

**cache**

*Default: null*

Store the results of processed files so that stylelint only operates on the changed ones. By default, the cache is stored in `./.stylelintcache` in `process.cwd()`. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--cache)).

**cache_location**

*Default: null*

Path to a file or directory for the cache location. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--cache-location)).

**formatter** / **custom_formatter**

*Default: null*

Specify the formatter to format your results. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--formatter--f----custom-formatter)).

**quiet**

*Default: null*

Only register violations for rules with an "error"-level severity (ignore "warning"-level). ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--quiet--q)).

**color**

*Default: null*

Force enabling/disabling of color. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--color---no-color)).

**report_needless_disables**

*Default: null*

Produce a report to clean up your codebase, keeping only the `stylelint-disable` comments that serve a purpose. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--report-needless-disables---rd)).

**report_invalid_scope_disables**

*Default: null*

Produce a report of the `stylelint-disable` comments that used for rules that don't exist within the configuration object. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--report-invalid-scope-disables---risd)).

**report_descriptionless_disables**

*Default: null*

Produce a report of the `stylelint-disable` comments without a description ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--report-descriptionless-disables---rdd)).

**max_warnings**

*Default: null*

Set a limit to the number of warnings accepted. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--max-warnings---mw)).

**output_file**

*Default: null*

Path of file to write a report. stylelint outputs the report to the specified `filename` in addition to the standard output. ([stylelint.io](https://stylelint.io/user-guide/usage/cli#--output-file--o)).

**other settings**

Any other stylelint settings should be able to be set through a [stylelint config file](https://stylelint.io/user-guide/configure).
