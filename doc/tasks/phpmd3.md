# PhpMd

The PhpMd3 task will sniff your code for bad coding standards.

It is the same as the `phpmd` task, but provides compatibility with the `3.x-dev` branch.
It will be merged into the `phpmd` task when the 3.x becomes stable.

***Composer***

```
composer require --dev phpmd/phpmd:3.x-dev
```

***Config***

The task lives under the `phpmd3` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpmd3:
            whitelist_patterns: []
            exclude: []
            report_format: text
            ruleset: ['phpmd.xml.dist']
            triggered_by: ['php']
```

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For example: whitelist files in `src/FolderA/` and `src/FolderB/` you can use 
```yaml
whitelist_patterns:
    - /^src\/FolderA\/(.*)/
    - /^src\/FolderB\/(.*)/
```

**exclude**

*Default: []*

This is a list of patterns that will be ignored by phpmd. With this option you can skip directories like tests. Leave this option blank to run phpmd for every php file.

**report_format**

*Default: text*

This sets the output [renderer](https://phpmd.org/documentation/#renderers) of phpmd.
Available formats: ansi, text.

**ruleset**

*Default: [phpmd.xml.dist]*

With this parameter you will be able to configure the rule/rulesets you want to use. You can use the standard
sets provided by PhpMd or you can configure your own xml configuration as described in the [PhpMd Documentation](https://phpmd.org/documentation/creating-a-ruleset.html)

The full list of rules/rulesets can be found at [PhpMd Rules](https://phpmd.org/rules/index.html)

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
