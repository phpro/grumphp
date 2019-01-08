# Notes

## tl;dr
- made some bigger changes to allow parallel execution of tasks
- currently only works for external tasks - internal tasks like the `linting` would have to be exposed as a command line executable
- introduced e2e/integration tests (because I don't know PhpSpec :D)
- for this "test", I added some depencies to `composer.json` and added a `grumphp_parallel.yml`,
  so you can run `composer install && vendor/bin/grumphp run --config="grumphp_parallel.yml"`
  to see the thing in action. I also add `verbosity` output to see the actual command, see
  `vendor/bin/grumphp run --config="grumphp_parallel.yml" -vvv` (messy, but helpful for understanding a task)
- this is a POC and I realise that I might have a somewhat different coding style, sorry for that.
  I don't cling to any of the implemenation, I just wanted to get something "runnable" to get an impression
  of how a parallel execution might look like and I think that this might actually be a pretty nice
  extension to `grumphp` :)

## Parallel execution
I updated the `TaskRunnerContext` to provide a `ParallelOptions` configuration object that can be defined via 
newly created config parameters (see `parameters.yml`);
````
  run_in_parallel: false
  parallel_process_limit: 2
  parallel_process_wait: 1
````
If `run_in_parallel` is true, the `TaskRunner` will run the processes in parallel. In order to make the output
helpful, I had to create a new `ParallProgressSubscriber`, because the tasks do now start/finish in random order.

It's not as "nice" as before, but I guess it serves as a POC. If we could bump `symfony/console` to support **only**
`v4`, we could even make use of the output sections (see https://symfony.com/doc/current/console.html#console-output-sections)
which would allow to update multiple progressbars "in place". 

Example of the current output
````
GrumPHP is sniffing your code!
Task 1/6: [Scheduling] PhpMdParallel
Task 2/6: [Scheduling] PhpStanParallel
Task 3/6: [Scheduling] PhpCpdParallel
Task 4/6: [Scheduling] SecurityCheckerParallel
Task 5/6: [Scheduling] ComposerParallel
Task 6/6: [Scheduling] ComposerRequireCheckerParallel
Task 1/6: [Running] PhpMdParallel
Task 2/6: [Running] PhpStanParallel
Task 3/6: [Running] PhpCpdParallel
Task 4/6: [Running] SecurityCheckerParallel
Task 4/6: [Success] SecurityCheckerParallel ✔
Task 5/6: [Running] ComposerParallel
Task 3/6: [Success] PhpCpdParallel ✔
Task 6/6: [Running] ComposerRequireCheckerParallel
Task 6/6: [Failed] ComposerRequireCheckerParallel ✘
Task 5/6: [Success] ComposerParallel ✔
Task 1/6: [Success] PhpMdParallel ✔
Task 2/6: [Failed] PhpStanParallel ✘
````

In order to make this work, I had to create a `ParallelTaskInterface` that provides a way to access the Symfony `Process`
and decouples the `run` steps, so that process generation, process running and process evaluation / result creation can be called 
separatly. 

I "translated" some tasks to `*Parallel` equivalents to make the necessary adjustments without having to remove the existing tasks.

I think that it is actually no necessary to keep those `TaskInterface` and `ParallelTaskInterface` separated, 
but I didn't want to change the existing `TaskInterface`.

## Tests
The new code is currently not covered by PhpSpec but only with Unit tests.
I'm not really familiar with PhpSpec and I wanted to get some feedback on the implementation
before I dig deeper into that.

I tried to be as "non-BC" as possible, that's why I'm doing a lot of "hacky" stuff in the
`GrumPHPHelperTrait` (e.g. accessing private mehthods etc.). Tbh, i think the application would
have to be refactored with unit testability in mind to make this "cleaner". Concrete Examples:
- add an optional construcutor argument (array) to Application that overrides the default grumphp.yml parsing for tests
- provide methods to "mock" stuff (tasks, services, parameters, ...) in the DI Container (see laravels facade-faking for instance)

... Or maybe I just don't know Symfony good enough ;)

### End2End tests
I'm a big fan of E2E tests, because they ensure that "the thing is actually working". On the downside, those
tests tend to be brittle and slow... But: machine time < dev time, imho ¯\_(ツ)_/¯

That being said, I introduced the `test/Helper/process_helper` executable as a lightweight "testing" process,
so that we can run "real" processes when testing the `TaskRunner`, for instance. Further, I added the `ExternalParallelTestTask` as
a wrapper for the `process_helper` as an easy means to "define" a deterministic task list without having to register them
upfront in the DI container.

### TaskRunnerTest
Goal was, to have an easy to understand test setup that fails if either the results or the output is unexpected.
The tests are not exhaustive but currently only cover the "happy path". Again - I wanted to get the general approval that
this is something that you would accept as a way of testing befor adding a better coverage.

### Task tests
When I implemented the `Paratest` task, I was facing the problem that there was basically "no easy way" to assert the actual output.
This might be due to my unfamiliarity with PhpSpec, though. But I just like to "see what actually happens" in a test, to assert
if it it's correct. Hence, I made some adjustments in the `AbstractExternalParallelTask` / `ParallelTaskInterface` class / interface 
to allow for easier testability, mainly:
- added `getExecutablePath()`
  - as a way to somehow access the `$externalCommandLocator` in order to get the resolve path
- added `resolveProcess()`
  - in order to get the `Process` of the corresponding task to resolve the "command line" of the process

Currently, the `$externalCommandLocator` can be "mocked" to control the return value. This allows the testing of commands
that are not actually pulled in as a dependency (because the external tools are only "suggestions" an no actual
dependencies). I would regard that rather as an "integration test"

However, it is also possible to leave the `$externalCommandLocator` in place, so that it acutally checks for the executable.
See `PhpCsTest::test_buildProcessWithInstalledDependency()` for an example.

This feels more like an actual End2End test AND one could even go a step further and actually "call" the command. This would
be benefitial to test the actual parameters (most commands will fail with an "unknown parameter" option, if the given
arguments are incorrect). But: This would require to pull in all supported tasks / external tools and would probably s
ignificantly increase the build time.

# Documentation

## `run` command
````
vendor/bin/grumphp run --help
Usage:
  run [options] [--] [<files>]...

Arguments:
  files                              (optional; overrides --file-provider) A list of files to be used. Example: file1 foo/bar/baz.php

Options:
      --testsuite=TESTSUITE          Specify which testsuite you want to run.
      --tasks=TASKS                  Specify which tasks you want to run (comma separated). Example: --tasks=task1,task2
      --passthru=PASSTHRU            The given string is appended to the underlying external command. Example: --passthru="--version --foo=bar"
      --file-provider=FILE-PROVIDER  The provider that resolves the files to check. Values: default,changed. Example: --file-provider="changed" [default: "default"]
````
## files 
You can provide a list of files that grumphp uses as input to run its tasks against. The argument is optional. If you do not provide 
a list of files, grumphp will resolve the files from the provider it finds in the `--file-provider` option. Example:

````
vendor/bin/grumphp run test.php dir/test.php file3
````


### --tasks=task1,task2
This options allows to execute only a subset of tasks that are configured in the `grumphp.yml` file.

### --passthru="--version"
Same ass the `passthru` parameter. This option is mainly useful in conjunction with `--tasks=new_task_to_check`
while exploring a specific task, because it allows to quickly modify the parameters. Example:

````
vendor/bin/grumphp run --passthru="--version" test.php
````

**Hint**: Run grumphp with the `-vv` flag to see the _actual_ command that is executed. The example above will 
result in a somthing like this:
````
vendor/bin/grumphp run --tasks=phpunit_parallel --config=grumphp_parallel.yml --passthru="--version" -vv test.php
[2019-01-08 18:21:51] GrumPHP.DEBUG: Repository created (git dir: "/codebase/grumphp/.git", working dir: "/codebase/grumphp") [] []
GrumPHP is sniffing your code!
Task 1/1: [Scheduling] PhpunitParallel (phpunit_parallel)
 >>>>> STARTING STAGE 0
Task 1/1: [Running] PhpunitParallel (phpunit_parallel)
Command: '/codebase/grumphp/vendor/bin/phpunit' --version

Task 1/1: [Success] PhpunitParallel (phpunit_parallel) ✔ (Runtime 1.04s)
 >>>>> FINISHING STAGE 0
````

**Caution**: This feature is currently only available to `*Parallel` tasks!

### --file-provider="changed"
Defines the provider that grumphp uses to resolve the files to check, if no `files` argument list is provided.
By default, all files in the current git repository are checked. Currently, two providers are supported:

- default
  - checks all files
- changed
  - checks all staged files of the git repository (see also `git:pre-commit` command)

## Parameters
````
parameters:
  run_in_parallel: false
  parallel_process_limit: 2
  parallel_process_wait: 1
  tasks:
    foo_task:
      metadata:
        stage: 200
        passthru: "--version"
````
## run_in_parallel (bool; false)
If true, grumphp runs in parallel mode. That means that individual external tasks are executed parallely. 
This setting does not affect a task that itself has settings for parallel execution (i.g. `phplint`). So it might be reasonable
to separate those tasks in different `stages` so that the system does not get overwhelmed with too many parallel processes.

**Caution**: This feature is _experimental_. Only a small number of tasks have been converted yet (look for the  `*Parallel` suffix).
Further, parallel execution currently only works for external tasks (e.g. `phpcs`) not for `Parser`- order `LinterTask`s.

## parallel_process_limit (int; 2)
If `run_in_parallel` is enabled, this setting denotes the maximum number of parallel running tasks.

## parallel_process_wait (int; 1)
If `run_in_parallel` is enabled, this setting denotes the time in seconds that the main process sleeps before checking if
any of the parallel running tasks have finished.

## stage (int; 0)
If `run_in_parallel` is enabled, this setting is used to group tasks together that should run parallely. This comes in handy
if tasks have parallel execution capabilities themselves or shoud not be run in parallel with other tasks because they might 
interfere with each other.

This setting is used in conjunction with `priority` to determine the execution order of tasks. I.e. the tasks are sorted descending
by `stage` and `priority` (stage taking precedence).

If a task is not converted for parallel execution yet, it will be executed at the end of the corresponding stage.

Consider the following config (`non_parallel` being the only task that is not converted for parallel execution):
````
parameters:
  run_in_parallel: true
  tasks:
    foo:
      metadata:
        priority: 100
        stage: 100
    bar:
      metadata:
        priority: 200
        stage: 100
    baz:
      metadata:
        priority: 100
        stage: 200
    buh:
      metadata:
        priority: 200
        stage: 200
    non_parallel:
      metadata:
        priority: 300
        stage: 200
````

This would result in the following execution plan:
````
> Stage: 200
buh (priority 200)
baz (priority 100)
non_parallel (priority 300)
> Stage: 100
bar (priority 200)
foo (priority 100)
````

Although the `non_parallel` task has the highest stage and priority value, it will only be executed at the end of the stage.

## passthru (string; "")
This string is appended verbatim to the end of the corresponding (external) tasks command. This might come in handy if the task in question
does not provide all arguments/options that the underlying command provides. The `passthru` parameter allows us to "use" those
arguments/options anyway. Please keep in mind that this is only meant as a workaround to facilitate adoption - **not** as 
a replacement for a proper PR :)

**Caution**: This feature is currently only available to `*Parallel` tasks!

Consider the following example:
````
parameters:
  run_in_parallel: true
  tasks:
    phpunit_parallel:
      metadata:
        passthru: "--version"
````

Although the `--version` option is not provided by grumphp, the resulting command will still look like this:
````
'vendor/bin/phpunit' --version
````

# ToDo
This implementation is currently a **POC** to show what grumphp is capable of. In order to actually "release" those changes,
(at least) the followings things should be done:
- translate all remaining external tasks to parallel tasks
- rename the ParallelTaskInterface to ExternalTaskInterface
- move some common functionality (passthru, stage) into the TaskInterface
- create `Spec`s for the newly created classes (e.g. the ParallelProgressSubscriber, the FileProviders, new methods in the TaskRunner, ...)
- resolve/clarify all `TODO` annotations/comments
- clean up the `GrumPHPHelperTrait`, e.g.:
  - don't create temp files for the config but provide an array (for instance)
  - remove the getting/setting/accessing of non-public properties/methods as much as possible
- extend End2End tests for the TaskRunner with more cases (e.g. early abortion)
