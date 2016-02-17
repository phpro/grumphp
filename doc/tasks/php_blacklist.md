# Php Blacklist

The Php Blacklist task will test your changes for blacklisted keywords, such as `die(`, `var_dump(` etc.
It lives under the `php_blacklist` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        php_blacklist:
            keywords:
                - "die("
                - "var_dump("
                - "->run("
                - "::var_export("
            visitors:
              - '@grumphp.parser.php.visitor.function_call'
              - '@grumphp.parser.php.visitor.concrete_method_call'
              - '@grumphp.parser.php.visitor.static_method_call'
            triggered_by: [php]
            ignore_patterns: []
```

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.
Currently there are three node visitors available:

- `function_call` to detect standard php function like `die(`
- `concrete_method_call` to detect concrete method call like `$foo->run(`
- `static_method_call` to detect static method call like `Foo::var_export(`

**visitors**

*Default: null*

Use this parameter to specify what code you want to blacklist. This is made possible by node visitors following PHP_Parser syntax.
Without any visitors specified, PHP Parser will only check code syntax (similar to php lint).

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the php blacklist task.
By default php blacklist will be triggered by altering a php file.
You can overwrite this option to whatever filetype you want to validate!

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by the PHP Parser.
With this option you can skip files like tests. Leave this option blank to run analysis for every php file.
