# ESLint ![fixer](https://img.shields.io/badge/-fixer-informational)

[ESLint](https://eslint.org/) is a static analysis tool for Javascript code. ESLint covers both code quality and coding style issues.

## NPM
If you'd like to install it globally:
```bash
npm -g eslint
```

If you'd like install it as a dev dependency of your project:
```bash
npm install eslint --save-dev
```

To generate a .eslintrc.* config file:
```
npx eslint --init
```

Done. See the ESLint [Getting Started](https://eslint.org/docs/user-guide/getting-started) guide for more info.

## Config
It lives under the `eslint` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        eslint:
            bin: node_modules/.bin/eslint
            triggered_by: [js, jsx, ts, tsx, vue]
            whitelist_patterns:
                - /^resources\/js\/(.*)/
            config: .eslintrc.json
            debug: false
            format: ~
            max_warnings: ~
            no_eslintrc: false
            quiet: false
```

**bin**

*Default: null*

The path to your eslint bin executable. Not necessary if eslint is in your $PATH. Can be used to specify path to project's eslint over globally installed eslint.


**triggered_by**

*Default: [js, jsx, ts, tsx, vue]*

This is a list of extensions which will trigger the ESLint task.


**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can specify the folders containing javascript files and thus skip folders like /tests/ or the /vendor/ directory. This option is used in conjunction with the parameter `triggered_by`.
For example: to whitelist files in `resources/js/` (Laravel's JS directory) and `assets/js/` (Symfony's JS directory) you can use:
```yml
whitelist_patterns:
  - /^resources\/js\/(.*)/
  - /^assets\/js\/(.*)/
```

**config**

*Default: null*

The path to your eslint's configuration file. Not necessary if using a standard eslintrc name, eg. .eslintrc.json, .eslint.js, or .eslint.yml

**debug**

*Default: false*

Turn on debug mode ([eslint.org](https://eslint.org/docs/user-guide/command-line-interface#debug)).

**format**

*Default: null*

Output format, eslint will use `stylish` by default. Other handy ones on cli are `compact`, `codeframe` and `table` (see full list on [eslint.org](https://eslint.org/docs/user-guide/formatters/)).

**max_warnings**

*Default: null*

Number of warnings (not errors) that are allowed before eslint exits with error status ([eslint.org](https://eslint.org/docs/user-guide/command-line-interface#max-warnings)).

**no_eslintrc**

*Default: false*

Set to true to ignore local .eslint config file ([eslint.org](https://eslint.org/docs/user-guide/command-line-interface#max-warnings)).

**quiet**

*Default: null*

Report errors only (no warnings). [eslint.org](https://eslint.org/docs/user-guide/command-line-interface#quiet)

**other settings**

Any other eslint settings (such as rules, env, ignore patterns, etc) should be able to be set through an [eslint config file](https://eslint.org/docs/user-guide/configuring) (instructions to generate a config file at top of document).
