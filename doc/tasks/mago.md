# Mago

The Mago task runs the Mago's toolchain.

***Composer***

```
composer require --dev carthage-software/mago
```

***Config***

The task lives under the `mago` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago:
            formatter: ~
            formatter_options: ~
            linter: ~
            linter_options: ~
            analyzer: ~
            analyzer_options: ~
            guard: ~
            guard_options: ~
```

**formatter**

*Default: `true`*

Enable the Mago's formatter.


**formatter_options**

*Default: `['--staged']`*

[Options](https://mago.carthage.software/tools/formatter/command-reference#options) for the `mago format` command.  
Each option must be an array's element.  
If the option needs a value, add it after the option name with an equal sign like this: `--option=value`.


**linter**

*Default: `true`*

Enable the Mago's linter.


**linter_options**

*Default: `['--staged']`*

[Options](https://mago.carthage.software/tools/linter/command-reference#options) for the `mago lint` command.  
Each option must be an array's element.  
If the option needs a value, add it after the option name with an equal sign like this: `--option=value`.


**analyzer**

*Default: `true`*

Enable the Mago's analyzer.


**analyzer_options**

*Default: `['--staged']`*

[Options](https://mago.carthage.software/tools/analyzer/command-reference#options) for the `mago analyze` command.  
Each option must be an array's element.  
If the option needs a value, add it after the option name with an equal sign like this: `--option=value`.


**guard**

*Default: `false`*

Enable the architectural guard.


**guard_options**

*Default: `[]`*

[Options](https://mago.carthage.software/tools/guard/command-reference#options) for the `mago guard` command.  
Each option must be an array's element.  
If the option needs a value, add it after the option name with an equal sign like this: `--option=value`.
