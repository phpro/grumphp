# Phpparser

The Phpparser task will test your changes for blacklisted or whitelisted keywords, such as `die()`, `var_dump()` etc.
It lives under the `php_parser` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        php_parser:
            visitors_options:
              function_call_visitor:
                blacklist: ["var_dump", "error_log"]
                whitelist: ["count", "get_class"]
              concrete_method_call_visitor:
                blacklist: ["print_r", "run"]
              static_method_call_visitor:
                blacklist: ["var_export"]
            visitors:
              - {visitor: '@grumphp.parser.php.visitor.function_call'}
              - {visitor: '@grumphp.parser.php.visitor.concrete_method_call'}
              - {visitor: '@grumphp.parser.php.visitor.static_method_call'}
            triggered_by: [php]
            ignore_patterns: []
```

**visitors_options**

Use this parameter to specify your blacklisted or whitelisted keywords list to find into, either:

- standard php function call
- concrete method calls
- static method call

*function_call_visitor: null*

Currently there is only one node visitors available:

- `grumphp.parser.php.visitor.function_call` to detect standard php function like `die()`

*concrete_method_call_visitor: null*

Currently there is only one node visitors available:

- `grumphp.parser.php.visitor.concrete_method_call` to detect concrete method call like `$foo->run()`

*static_method_call_visitor: null*

Currently there is only one node visitors available:

- `grumphp.parser.php.visitor.static_method_call` to detect static method call like `Foo::var_export()`

**visitors**

*Default: null*

Use this parameter to specify what code you want to scan. This is made possible by node visitors following PHP_Parser syntax.
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
