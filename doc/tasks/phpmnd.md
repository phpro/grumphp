# PhpMnd

The PhpMnd task helps you detect magic numbers in PHP code.

***Composer***

```
composer require --dev povils/phpmnd
```

***Config***

The task lives under the `phpmnd` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpmnd:
            directory: .
            whitelist_patterns: []
            exclude: []
            exclude_name: []
            exclude_path: []
            extensions: []
            hint: false
            ignore_numbers: []
            ignore_strings: []
            strings: false
            triggered_by: ['php']
```

**directory**

*Default: .*

With this parameter you can define which directory you want to run `phpmnd` in (must be relative to cwd).

**whitelist_patterns**

*Default: []*

This is a list of regex patterns that will filter files to validate. With this option you can skip files like tests. This option is used in relation with the parameter `triggered_by`.
For exemple to validate only files in your `src/App/` and `src/AppBundle/` directories in a Symfony you can use 
```yml
whitelist_patterns:
  - /^src\/App\/(.*)/
  - /^src\/AppBundle\/(.*)/
```

**exclude**

*Default: []*

This parameter will allow you to exclude directories from the code analysis (must be relative to source).

**exclude_name**

*Default: []*

This parameter will allow you to exclude files from the code analysis (must be relative to source).

**exclude_path**

*Default: []*

This parameter will allow you to exclude paths from the code analysis (must be relative to source).

**extensions**

*Default: []*

By default PHP Magic Number Detector analyses conditions, return statements and switch cases. This parameter lets you extend the code analysis.

* **argument**
```php
round($number, 4);
```
* **array**
```php
$array = [200, 201];
```
* **assign**
```php
$var = 10;
```
* **default_parameter**
```php
function foo($default = 3);
```
* **operation**
```php
$bar = $foo * 20;
```
* **property**
```php
private $bar = 10;
```
* **return(default)**
```php
return 5;
```
* **condition(default)**
```php
$var < 7;
```
* **switch_case(default)**
```php
case 3;
```

You can use `all` to include all extensions. If an extension starts with minus (`-`) that means it will be removed from the code analysis.

**hint**

*Default: false*

This parameter will suggest replacements for magic numbers based on your codebase constants.

**ignore_numbers**

*Default: []*

This parameter will exclude numbers from the code analysis. By default PHP Magic Number Detector does not consider `0` and `1` to be magic numbers.

**ignore_strings**

*Default: []*

This parameter will exclude strings from the code analysis when the parameter "strings" is enabled.

**strings**

*Default: false*

This parameter will include strings literal search in the code analysis.

**triggered_by**

*Default: [php]*

This is a list of extensions to be analyzed.
