# Phpcs

The Phpcs task will sniff your code for bad coding standards.
It lives under the `phpcs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: PSR2
            show_warnings: true
            tab_width: ~
            ignore_patterns: []
            sniffs: []
            
```

**standard**

*Default: PSR2*

This parameter will describe which standard is being used to validate your code for bad coding standards.


**show_warnings**

*Default: true*

Triggers an error when there are warnings.


**tab_width**

*Default: null*

By default, the standard will specify the optimal tab-width of the code. If you want to overwrite this option, you can use this configuration option.


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpcs. With this option you can skip files like tests. Leave this option blank to run phpcs for every php file.


**sniffs**

*Default: []*

This is a list of sniffs that need to be executed. Leave this option blank to run all configured sniffs for the selected standard.

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
            show_warnings: false
```
