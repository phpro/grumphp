# Test Suites
Don't want to run all the tests during a commit?
Do you want to specify some pre-defined tasks you want to run?
It is easy to configure and run custom testsuites in GrumPHP
Test suites live under their own namespace in the parameters part.


```yaml
# grumphp.yml
grumphp:
    testsuites:
        suitename:
            tasks:
              - phpcs
              - phpspec
```

It is possible to define multiple testsuites in the `grumphp.yml` file.
Every test-suite has a unique name that can be used in the run command.
A test-suite has following parameters:


**tasks**

*Default: []*

A test-suite consists of a list of tasks that should be executed.
You can use any registered task name in the list of tasks.
The list is validated against this list of registered tasks. 
When you enter an unknown task, an error will be thrown.


## Overriding git hook test-suites
To make it possible to define which tests should run during a git hook,
we made it possible to use one of following pre-defined test suites:

```yaml
# grumphp.yml
grumphp:
    testsuites:
        # Specify the test-suite for the git:commit-msg command:
        git_commit_msg:
            tasks: []
        # Specify the test-suite for the git:pre-commit command:
        git_pre_commit:
            tasks: []
```
