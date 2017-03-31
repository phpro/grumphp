# PHP-CS-Fixer

The PHP-CS-Fixer task will run codestyle checks.

***Composer***

```
composer require --dev friendsofphp/php-cs-fixer
```

***Config***

The task lives under the `phpcsfixer` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcsfixer:
            config_file: ~
            config: ~
            fixers: []
            level: ~
            verbose: true
```

**config_file**

*Default: null*

You can specify the path to the `.php_cs` file.


**config**

*Default: 'default'*

There such predefined configs for codestyle checks: `default`, `magento`, `sf23`.
If you want to run a particular config, specify it with this option.


**fixers**

*Default: array()*

There are a lot of fixers which you can apply to your code. You can specify an array of them in this config.
The full list of fixers you can find [here](https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage).


**level**

*Default: null*

Fixers are grouped by levels: `psr0`, `psr1`, `psr2` you can specify a group instead of applying them separately.


**verbose**

*Default: true*

Show applied fixers.
