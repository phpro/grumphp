# PHPArkitect

PHPArkitect helps you to keep your PHP codebase coherent and solid, by permitting to add some architectural constraint check to your workflow.
It lives under the `phparkitect` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev phparkitect/phparkitect
```

## Config
```yaml
# grumphp.yml
grumphp:
    tasks:
      phparkitect:
            configuration: ~
            target_php_version: ~
            stop_on_failure: ~ 
```

**configuration**

*Default: null*

With this parameter you can specify the path your project's configuration file.
By defaykt PHPArkitect will search all rules in phparkitect.php located in the root of your project.

**target_php_version**

*Default: null*

With this parameter, you can specify which PHP version should use the parser.
This can be useful to debug problems and to understand if there are problems with a different PHP version.

**stop_on_failure**

*Default: null*

With this option the process will end immediately after the first violation.
