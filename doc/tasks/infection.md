# Infection

Infection is a PHP mutation testing framework based on Abstract Syntax Tree.

***Composer***

```
composer require --dev infection/infection
```

***Config***

It lives under the `infection` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        infection:
            threads: ~
            test_framework: ~
            only_covered: false
            configuration: ~
            min_msi: ~
            min_covered_msi: ~
            mutators: []
            ignore_patterns: []
            triggered_by: [php]
```

**threads**

*Default: null*

If you want to run tests for mutated code in parallel, set this to something bigger than 1.
It will dramatically speed up the mutation process.
Please note that if your tests somehow depends on each other or use a database, this option can lead
to failing tests which give many false-positives results.


**test_framework**

*Default: null*

This is the name of a test framework to use. Currently Infection supports `PhpUnit` and `PhpSpec`.


**only_covered**

*Default: false*

Run the mutation testing only for covered by tests files.


**configuration**

*Default: null*

The path or name to the infection configuration file.


**min_msi**

*Default: null*

This is a minimum threshold of Mutation Score Indicator (MSI) in percentage.


**min_covered_msi**

*Default: null*

This is a minimum threshold of Covered Code Mutation Score Indicator (MSI) in percentage.


**mutators**

*Default: []*

This is a list separated options to specify a particular set of mutators that needs to be executed. 


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by Infection.
With this option you can skip files like tests. Leave this option blank to run analysis for all
'triggered by' files.


**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the infection task.
By default infection will be triggered by altering a php file. 
You can overwrite this option to whatever file you want to use!
