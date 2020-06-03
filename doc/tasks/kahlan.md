# Kahlan

Kahlan is a full-featured Unit & BDD test framework a la RSpec/JSpec which uses a describe-it syntax and moves testing in PHP one step forward.

***Composer***

```
composer require --dev kahlan/kahlan
```

***Config***

The task lives under the `kahlan` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        kahlan:
          config: ~
          src: ~
          spec: ~
          pattern: ~
          reporter: ~
          coverage: ~
          clover: ~
          istanbul: ~
          lcov: ~
          ff: ~
          no_colors: ~
          no_header: ~
          include: ~
          exclude: ~
          persistent: ~
          cc: ~
          autoclear: ~
```

**config**

*Default: `kahlan-config.php`*

The PHP configuration file to use


**src**

*Default: `['src']`*

Paths of source directories


**spec**

*Default: `['spec']`*

Paths of specification directories


**pattern**

*Default: `*Spec.php`*

A shell wildcard pattern


**reporter**

*Default: null*

The name of the text reporter to use, the built-in text reporters
are `'dot'`, `'bar'`, `'json'`, `'tap'` & `'verbose'`.
You can optionally redirect the reporter output to a file by using the
colon syntax (multiple --reporter options are also supported).


**coverage**

*Default: null*

Generate code coverage report. The value specify the level of
detail for the code coverage report (0-4). If a namespace, class, or
method definition is provided, it will generate a detailed code
coverage of this specific scope.


**clover**

*Default: null*

Export code coverage report into a Clover XML format.


**istanbul**

*Default: null*

Export code coverage report into an istanbul compatible JSON format.


**lcov**

*Default: null*

Export code coverage report into a lcov compatible text format.


**ff**

*Default: 0*

Fast fail option. `0` mean unlimited


**no_colors**

*Default: `false`*

To turn off colors.


**no_header**

*Default: `false`*

To turn off header.


**include**

*Default: `['*']`*

Paths to include for patching. 


**exlude**

*Default: `[]`*

Paths to exclude for patching. 


**persistent**

*Default: true*

Cache patched files.


**cc**

*Default: false*

Clear cache before spec run. 


**autoclear**

*Default: `['Kahlan\Plugin\Monkey','Kahlan\Plugin\Call','Kahlan\Plugin\Stub','Kahlan\Plugin\Quit']`*

Classes to autoclear after each spec 

