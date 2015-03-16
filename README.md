# GrumPHP
Sick and tired of complaining about your co-workers code? GrumPHP will do it for you!
 This composer plugin will register some git hooks in you package repository.
 When somebody commits his changes, GrumPHP will run some tests on the committed code.
 If the tests fail, you won't be able to commit your changes.
 This handy tool will make your co-workers care about their code.
 
GrumPHP has a set of common tasks build-in. You will be able to use GrumPHP with a minimum of configuration.

We don't want to bore you with all the details, so quick: install it yourself and unleesh the power of GrumPHP!

# Installation

This package is composer plugin and should be installed to your project's dev dependency using composer:

```
composer require --dev phpro/grumphp:~0.1
```

When the package is installed, GrumPHP will attach itself to the GIT hooks of your project. You will see following message in the composer logs:

*Watch out! GrumPHP is sniffing your commits!*

To make GrumPHP even more awesome, it will suggest installing some extra packages:

- squizlabs/php_codesniffer : ~2.3
- phpspec/phpspec : ~2.1
- roave/security-advisors : dev-master@dev

GrumPHP will never push you in using a specific task. You can choose the tasks that fits your needs, and activate it in no time!

# Configuration

`Sample grumphp.yml`:

```
parameters:
    bin_dir: "./vendor/bin"
    git_dir: "."
    tasks:
        phpcs:
            standard: "PSR2"
        phpspec: ~
```

## Parameters
**bin_dir**

*Default: ./bin/vendor*

This parameter will tell GrumPHP where it can locate external commando's like phpcs and phpspec. It defaults to the default composer bin directory.


**git_dir**

*Default: .*

This parameter will tell GrumPHP in which folder it can find the .git folder. This parameter is used to create the git hooks at the correct location. It defaults to the working directory.

## Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

```
parameters:
    tasks:
        phpcs: ~
        phpspec: ~
```

Every task has it's own default configuration. It is possible to overwrite the parameters per task.

### Phpcs

The Phpcs task will sniff your code for bad coding standards. It lives under the `phpcs` namespace and has following configurable parameters:

**standard**

*Default: PSR2*

This parameter will describe which standard is being used to validate your code for bad conding standards.


### Phpspec

The Phpspec task will spec your code with Phpspec. It lives under `the phpspec` namespace and has following configurable parameters:

*No parameters available yet*

### Custom tasks

It is very easy to configure your own project specific task. You just have to create a class that implements the `GrumPHP\Task\TaskInterface`.
Next register it to the service manager and add your task configuration:

```
parameters:
    tasks:
        myConfigKey:
            config1: config-value
            
services:
    task.myCustomTask:
        class: My\Custom\Task
        arguments:
          - @config
          - "@=parameter('tasks')['myConfigKey'] ? parameter('tasks')['myConfigKey'] : []"
        tags:
          - {name: grumphp.task, config: myConfigKey}
```

**Note:** You do NOT have to add the main and task configuration. This example just shows you how to do it. You're welcome!

You just registered your custom task in no time! Pretty cool right?!

### Roadmap

Following tasks are still on the roadmap:

- phpunit
- behat
- composer.json and composer.lock need to be committed ad the same time
- ...
