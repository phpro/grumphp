# PhpMd

The PhpMd task will sniff your code for bad coding standards.
It lives under the `phpmd` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpmd:
            ruleset: ['cleancode', 'codesize', 'naming']
            triggered_by: ['php']
```

**ruleset**

*Default: cleancode,codesize,naming*

With this parameter you will be able to configure the rule/rulesets you want to use. You can use the standard 
sets provided by PhpMd or you can configure your own xml configuration as described in the [PhpMd Documentation](https://phpmd.org/documentation/creating-a-ruleset.html)

The full list of rules/rulesets can be found at [PhpMd Rules](https://phpmd.org/rules/index.html) 

**triggered_by**

*Default: [php]:

This is a list of extensions to be sniffed. 
