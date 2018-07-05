# PhpCpd

The PhpCpd task will sniff your code for duplicated lines.

***Composer***

```
composer require --dev sebastian/phpcpd
```

***Config***

The task lives under the `phpcpd` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcpd:
            directory: '.'
            exclude: ['vendor']
            names_exclude: []
            regexps_exclude: []
            fuzzy: false
            min_lines: 5
            min_tokens: 70
            triggered_by: ['php']
```

**directory**

*Default: .*

With this parameter you can define which directory you want to run `phpcpd` in (must be relative to cwd).

**exclude**

*Default: [vendor]*

With this parameter you will be able to exclude one or multiple directories from code analysis (must be relative to `directory`).

**names_exclude**

*Default: []*

With this parameter you will be able to exclude one or multiple files from code analysis.
The values of this option refer to the file names, not to the paths.

You can use a regexp, a glob, or a string.

**regexps_exclude**

*Default: []*

With this parameter you will be able to exclude one or multiple files from code analysis.

You can use patterns (delimited with / sign) or simple strings.

**fuzzy**

*Default: false*

With this parameter you will be able to fuzz variable names.

**min_lines**

*Default: 5*

With this parameter you will be able to set minimum number of identical lines.

**min_tokens**

*Default: 70*

With this parameter you will be able to set minimum number of identical tokens.

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
