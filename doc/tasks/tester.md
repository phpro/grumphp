# Tester

The Netter Tester task will run your unit tests.

***Composer***

```
composer require nette/tester --dev
```

***Config***

The task lives under the `tester` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        tester:
            path: "."
            always_execute: false
            log: ~
            show_information_about_skipped_tests: false
            stop_on_fail: false
            parallel_processes: ~
            output: ~
            temp: ~
            setup: ~
            colors: ~
            coverage: ~
            coverage_src: ~
```

**path**

*Default: .*

The test directory that contains the tests that need to be executed.

**always_execute**

*Default: false*

Always run the whole test suite, even if no PHP files were changed.

**log**

*Default: null*

You can wite the testing progress to a file.

**show_information_about_skipped_tests**

*Default: false*

When this option is set to `true`, the task will show information about skipped tests.

**stop_on_fail**

*Default: false*

When this option is set to `true`, the task will stop after the first failing test.

**parallel_processes**

*Default: null*
The tests run in parallel processes. By default this value is `8`. If you wish to run tests in series, use `1`.

**output**

*Default: null*

You can choose the format of the output. The available options are `console`, `tap`, `machine`, `junit` and `none`. By default the format is `console`.

**temp**
   
*Default: null*

Sets a path to directory for temporary files of Tester. The Default value is `sys_get_temp_dir()`.
            
**setup**

*Default: null*

The Tester loads the given PHP script on start.
            
**colors**

*Default: null*

You can disable color in terminal by setting the value to `0`, the default is `1`.
            
**coverage**

*Default: null*

Generate a report. The file extension determines the contents format. Only HTML or Clover reports are supported.
Example: `coverage.html` or `coverage.xml`
            
**coverage_src**

*Default: null*

This is issued with the `coverage` option. This is a path to the source code for which we generate the report.