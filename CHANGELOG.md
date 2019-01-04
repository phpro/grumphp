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
`GrumPhpHelperTrait` (e.g. accessing private mehthods etc.). Tbh, i think the application would
have to be refactored with unit testability in mind to make this "cleaner". Concrete Examples:
- add an optional construcutor argument (array) to Application that overrides the default grumphp.yml parsing for tests
- provide methods to "mock" stuff (tasks, services, parameters, ...) in the DI Container (see laravels facade-faking for instance)

... Or maybe I just don't know Symfony good enough ;)

### End2End tests
I'm a big fan of E2E tests, because they ensure that "the thing is actually working". On the downside, those
tests tend to be brittle and slow... But: machine time < dev time, imho ¯\_(ツ)_/¯

That being said, I introduced the `test/Helper/process_helper` executable as a lightweight "testing" process,
so that we can run "real" processes when testing the `TaskRunner`, for instance. Further, I added the `ExternalTestTask` as
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
