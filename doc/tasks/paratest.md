# Paratest

The Paratest task will run your PHPUnit tests in parallel.

***Composer***

```
composer require --dev brianium/paratest
```

***Config***

The task lives under the `paratest` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        paratest:
            processes: ~
            functional: ~
            group: []
            always_execute: false
            phpunit: null
            configuration: null
            runner: null
            debugger: null
            coverage-clover: null
            coverage-html: null
            coverage-php: null
            coverage-xml: null
            log-junit: null
            testsuite: null
            verbose: false
```

**processes**

*Default: null*

The number of test processes to run. (Default: auto)
 Possible values:
 - Integer (>= 1): Number of processes to run.
 - auto (default): Number of processes is automatically set to the number of logical CPU cores.
 - half: Number of processes is automatically set to half the number of logical CPU cores.

**functional**

*Default: null*

Run test methods instead of classes in separate processes.


**group**

*Default: array()*

If you wish to only run tests from a certain Group.
`group: [fast,quick,small]`


**always_execute**

*Default: false*

Always run the whole test suite, even if no PHP files were changed.

**phpunit**

*Default: null*

The PHPUnit binary to execute. (Default: vendor/bin/phpunit)

**configuration**

*Default: null*

The PHPUnit configuration file to use.

**runner**

*Default: null*

Runner, WrapperRunner or SqliteRunner. (Default: Runner)

**coverage-clover**

*Default: null*

Generate code coverage report in Clover XML format.

**coverage-html**

*Default: null*

Generate code coverage report in HTML format.

**coverage-php**

*Default: null*

Serialize PHP_CodeCoverage object to file.

**coverage-xml**

*Default: null*

Generate code coverage report in PHPUnit XML format.

**log-junit**

*Default: null*

Log test execution in JUnit XML format to file.

**testsuite**

*Default: null*

Filter which testsuite to run. Run multiple suits by separating them with ",". Example:  --testsuite suite1,suite2


**verbose**

*Default: false*

Adds additional debugging information to the screen when running paratest.


