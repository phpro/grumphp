# PHP parser

The PHP parser task will run static code analyses on your PHP code.
You can specify which code visitors should run on your code or write your own code visitor.
 
 ***Composer***
 
 ```
 composer require --dev nikic/php-parser
 ```
 
 ***Config***
 
The task lives under the `php_parser` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            ignore_patterns: []
            kind: php7
            visitors: {}
            triggered_by: [php]
```

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by the PHP Parser.
With this option you can skip files like tests. Leave this option blank to run analysis for every php file.

**kind**

*Default: php7*

Can be one of: php5, php7.
This option determines which Lexer the PHP_Parser uses to tokenize the PHP code.
By default the PREFER_PHP7 is loaded.

**visitors**

*Default: {}*

Use this parameter to specify what code you want to scan. This is made possible by node visitors following PHP_Parser syntax.
Without any visitors specified, PHP Parser will only check code syntax (similar to php lint).
In the next chapter, you can find a list of built-in visitors. 
It's also possible to write your own visitor!

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the php blacklist task.
By default php blacklist will be triggered by altering a php file.
You can overwrite this option to whatever filetype you want to validate!

## Built-in visitors

- [declare_strict_types](#declare_strict_types)
- [forbidden_class_method_calls](#forbidden_class_method_calls)
- [forbidden_function_calls](#forbidden_function_calls)
- [forbidden_static_method_calls](#forbidden_static_method_calls)
- [nameresolver](#nameresolver)
- [never_use_else](#never_use_else)
- [no_exit_statements](#no_exit_statements)

### declare_strict_types

This visitor can be used to enforce `declare(strict_types=1)` in every PHP file.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                declare_strict_types: ~
```

This visitore is not configurable!


### forbidden_class_method_calls

This visitor can be used to look for forbidden class method calls.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                forbidden_class_method_calls: 
                    blacklist:
                        - '$dumper->dump'
```

**blacklist**

*Default: []*

This is a list of blacklisted class method calls. The syntax is `$variableName->methodName`.
When one of the functions inside this list is being called by your code, 
the parser will markt this method as an error.


### forbidden_function_calls

This visitor can be used to look for forbidden function calls.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                forbidden_function_calls: 
                    blacklist: 
                        - 'var_dump'
```

**blacklist**

*Default: []*

This is a list of blacklisted function calls.
When one of the functions inside this list is being called by your code, 
the parser will markt this method as an error.

*Note* that statements like `die()` and `exit` are not functions but exit nodes. You can validate these statements by adding the [`no_exit_statements`](https://github.com/phpro/grumphp/blob/master/doc/tasks/phpparser.md#no_exit_statements) visitor to your configuration.

### forbidden_static_method_calls

This visitor can be used to look for forbidden static method calls.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                forbidden_static_method_calls: 
                    blacklist:
                        - 'Dumper::dump'
```

**blacklist**

*Default: []*

This is a list of blacklisted static method calls. The syntax is `Fully\Qualified\ClassName::staticMethodName`.
When one of the functions inside this list is being called by your code, 
the parser will markt this method as an error.


### nameresolver

This visitor is an alias for the built-in PhpParser NameResolver visitor.
It looks for class aliases in your code and adds the alias as an attribute to the class nodes.

*Note:* This visitor is enabled by default since it is used by other visitors. 
You don't have to register it in the task configuration.

This visitor is not configurable!


### never_use_else

This visitor will search for the `else` and `elseif` keywords in your code.
An error will be added if one of those statements is found.
More information about Object Calisthenics can be found 
[here](http://www.slideshare.net/rdohms/your-code-sucks-lets-fix-it-15471808) 
and 
[here](http://www.slideshare.net/guilhermeblanco/object-calisthenics-applied-to-php).

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                never_use_else: ~ 
```

This visitor is not configurable!


### no_exit_statements

This visitor will search for exit statements like `die()` or `exit` in your code.
An error will be added if an exit statement is found.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                no_exit_statements: ~ 
```

This visitor is not configurable!


## Creating your own visitor

Creating your own visitor is easy!
Just create a class that implements the `PhpParser\NodeVisitor` interface:

```php
// PhpParser\NodeVisitor
interface NodeVisitor
{
    public function beforeTraverse(array $nodes);
    public function enterNode(Node $node);
    public function leaveNode(Node $node);
    public function afterTraverse(array $nodes);
}
```

Once you've written your visitor, you'll have to register it to the service container:

```yaml
services:
    grumphp.parser.php.visitor.your_visitor:
      class: 'Your\Visitor\Class'
      arguments: []
      tags:
        - {name: 'php_parser.visitor', alias: 'your_visitor'}

```

Since we use the service container, you are able to inject the dependencies you need with the `arguments` attribute.
The `php_parser.visitor` tag will make your class available in GrumPHP.
Tha alias `your_visitor` can now be set as a visitor in the phpparser task:

```yml
# grumphp.yml
parameters:
    tasks:
        phpparser:
            visitors:
                your_visitor: ~ 
```

### Stateless visitors

An important note on the visitors is that they are completely stateless!
If you've already written a PhpParser Visitor before, you know that advanced visitors will contain a specific state about the class.
When scanning the next file, the state needs to be reset.

In our implementation, we've chosen to always create a new visitor for every file.
This way, you don't have to think about clearing the state of a visitor on each run.
This will result in easy and understandable visitors!


### Optional interfaces and classes

We also added some optional interfaces and to make it easier to interct with the GrumPHP context:


**ConfigurableVisitorInterface**

The `ConfigurableVisitorInterface` allows you to make the visitor configurable in the `grumphp.yml` file.
To make sure the visitor works as you please, It is recommended to use the `OptionsResolver` to validate the configured options. 


```php
// GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
interface ConfigurableVisitorInterface extends NodeVisitor
{
    public function configure(array $options);
}
```


**ContextAwareVisitorInterface**

The `ContextAwareVisitorInterface` will make your task aware of the context the visitor is running in.
The `ParserContext` object will have access to the file information and the errors collection.
This last one can be used to add a new error in your visitor.

```php
// GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface
interface ContextAwareVisitorInterface extends NodeVisitor
{
    public function setContext(ParserContext $context);
}
```

**AbstractVisitor**

In the built-in visitors, we use the `AbstractVisitor` that extends the `PhpParser\NodeVisitorAbstract`.
This means that you only have to implement the methods from the `NodeVisitor` that you need.
It also implements the `ContextAwareVisitorInterface` and provides an easy method for logging errors in your custom visitor.
The bleuprint of this abstract visitor looks like this:


```php
// GrumPHP\Parser\Php\Visitor\AbstractVisitor;
class AbstractVisitor extends NodeVisitorAbstract implements ContextAwareVisitorInterface
{
    protected $context;

    public function setContext(ParserContext $context);

    protected function addError($message, $line = -1, $type = ParseError::TYPE_ERROR);
}

```
