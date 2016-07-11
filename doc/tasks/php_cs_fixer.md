# PHP-CS-Fixer

The PHP-CS-Fixer task will run codestyle checks.
It lives under the `phpcsfixer` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcsfixer:
            config: ~
            pathMode: override
            rules: []
            verbose: true
```

**config**

*Default: null*

You can specify the path to the `.php_cs` file.

**pathMode**

*Default: override*

You can specify the path mode.


**rules**

*Default: array()*

There are a lot of rules which you can apply to your code. You can specify an array of them in this config.
The full list of rules you can find [here](https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage).


**verbose**

*Default: true*

Show applied fixers.
