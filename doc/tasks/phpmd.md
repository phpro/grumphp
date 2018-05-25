# PhpMd

The PhpMd task will sniff your code for bad coding standards.

***Composer***

```
composer require --dev phpmd/phpmd
```

***Config***

The task lives under the `phpmd` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpmd:
            whitelist_patterns: []
            exclude: []
            ruleset: ['cleancode', 'codesize', 'naming']
            triggered_by: ['php']
```

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For example: whitelist files in `src/FolderA/` and `src/FolderB/` you can use 
```yml
whitelist_patterns:
  - /^src\/FolderA\/(.*)/
  - /^src\/FolderB\/(.*)/
```

**exclude**

*Default: []*

This is a list of patterns that will be ignored by phpmd. With this option you can skip directories like tests. Leave this option blank to run phpmd for every php file.

**ruleset**

*Default: [cleancode,codesize,naming]*

With this parameter you will be able to configure the rule/rulesets you want to use. You can use the standard
sets provided by PhpMd or you can configure your own xml configuration as described in the [PhpMd Documentation](https://phpmd.org/documentation/creating-a-ruleset.html)

The full list of rules/rulesets can be found at [PhpMd Rules](https://phpmd.org/rules/index.html)

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
