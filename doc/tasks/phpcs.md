# Phpcs

The Phpcs task will sniff your code for bad coding standards.

***Composer***

```
composer require --dev squizlabs/php_codesniffer
```

***Config***

The task lives under the `phpcs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: []
            severity: ~
            error_severity: ~
            warning_severity: ~
            tab_width: ~
            report: full
            report_width: ~
            whitelist_patterns: []
            encoding: ~
            ignore_patterns: []
            sniffs: []
            triggered_by: [php]
            
```

**standard**

*Default: []*

This parameter will describe which standard/s is being used to validate your code for bad coding standards.
By default it is set to null so that the Phpcs defaults are being used.
Phpcs will be using the PEAR or local `phpcs.xml` standard by default.
You can configure this task to use any standard supported by the Phpcs CLI.
For Example: `PEAR`, `PHPCS`, `PSR1`, `PSR2`, `Squiz` and `Zend`

You can get a list of all installed phpcs standards with the command:

```sh
phpcs -i
```

**severity**

*Default: null*

Global severity level that should be used by `phpcs` (default is 5).

**error_severity**

*Default: null*

Error severity that should be used by `phpcs` (default is 5).

**warning_severity**

*Default: null*

Warning severity that should be used by `phpcs` (default is 5).

**tab_width**

*Default: null*

By default, the standard will specify the optimal tab-width of the code. If you want to overwrite this option, you can use this configuration option.

**encoding**

*Default: null*

The default encoding used by PHP_CodeSniffer (is ISO-8859-1).

**report**

*Default: full*

The report type output by PHP_CodeSniffer, put `code` to see a code snippet of the offending code.
Consult the [complete list](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#setting-the-default-report-format) for more formats.

**report_width**

*Default: null*

PHP_CodeSniffer will print all screen-based reports 80 characters wide. You may override this size so that long lines do not wrap.

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For exemple to validate only files in your `src/App/` and `src/AppBundle/` directories in a Symfony you can use 
```yml
whitelist_patterns:
  - /^src\/App\/(.*)/
  - /^src\/AppBundle\/(.*)/
```


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpcs. With this option you can skip files like tests. Leave this option blank to run phpcs for every php file.


**sniffs**

*Default: []*

This is a list of sniffs that need to be executed. Leave this option blank to run all configured sniffs for the selected standard.

**triggered_by**

*Default: [php]:

This is a list of extensions to be sniffed. 

## Framework presets

### Symfony 2

If you want to use Phpcs for your Symfony2 projects, you can require the leanpub phpcs repo.

```sh
composer require --dev leaphub/phpcs-symfony2-standard
```

Following this, you can add the path to your phpcs task.

```yml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: "vendor/leaphub/phpcs-symfony2-standard/leaphub/phpcs/Symfony2/"
```

### Magento 

If you want to use Phpcs for your Magento projects, you can require the magento-ecg repo.

```sh
composer require --dev magento-ecg/coding-standard
```

Following this, you can add the path to your phpcs task.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: "vendor/magento-ecg/coding-standard/Ecg/"
            warning_severity: 0
```

### Drupal

If you want to use Phpcs for your Drupal projects, you can require the Drupal Code Sniffer (Coder)

```sh
composer require --dev drupal/coder
```

Following this, you can add the path to your phpcs task.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: 
              - vendor/drupal/coder/coder_sniffer/Drupal
              - vendor/drupal/coder/coder_sniffer/DrupalPractice
            ignore_patterns:
              - cfg/
              - libraries/
            triggered_by:
              - php
              - module
              - inc
```
