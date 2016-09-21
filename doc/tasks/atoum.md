# Atoum

The Atoum task will run your unit tests.
It lives under the `atoum` namespace and has the following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        atoum:
            config_file: .atoum.php
            bootstrap_file: tests/units/bootstrap.php
            directories:
                - tests/units
            files:
                - tests/units/MyTest.php
            namespaces:
                - mageekguy\\atoum\\tests\\units\\asserters
            methods:
                - mageekguy\\atoum\\tests\\units\\asserters\\string::testContains
                - mageekguy\\atoum\\tests\\units\\asserters\\string::*
            tags:
                - thisIsOneTag
                - thisIsThreeTag
```

**config_file**

*Default: null*

The path to your configuration file. If you name your configuration file .atoum.php, atoum will load it automatically if this file is located in the current directory. The config_file parameter is optional in this case.

[See atoum documentation](http://docs.atoum.org/en/latest/configuration_bootstraping.html#configuration-file)

**bootstrap_file**

*Default: null*

The path to your bootstrap file if you need any.

[See atoum documentation](http://docs.atoum.org/en/latest/configuration_bootstraping.html#bootstrap-file)

**directories**

*Default: []*

If you want to limit the execution of the unit tests to certain directories, list them here.

[See atoum documentation](http://docs.atoum.org/en/latest/running_tests.html#by-folders)

**files**

*Default: []*

If you want to limit the execution of the unit tests to certain files, list them here.

[See atoum documentation](http://docs.atoum.org/en/latest/running_tests.html#by-files)

**namespaces**

*Default: []*

If you want to limit the execution of the unit tests to certain namespaces, list them here.

[See atoum documentation](http://docs.atoum.org/en/latest/running_tests.html#by-namespace)

**methods**

*Default: []*

If you want to limit the execution of the unit tests to certain methods or classes, list them here.

[See atoum documentation](http://docs.atoum.org/en/latest/running_tests.html#a-class-or-a-method)

**tags**

*Default: []*

If you want to limit the execution of the unit tests to certain tags, list them here.

[See atoum documentation](http://docs.atoum.org/en/latest/running_tests.html#tags)
