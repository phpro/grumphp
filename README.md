[![Build status](https://api.travis-ci.org/phpro/grumphp.svg)](http://travis-ci.org/phpro/grumphp)
[![Insight](https://img.shields.io/sensiolabs/i/9a345021-c8a1-4f48-948a-d15de51d9909.svg)](https://insight.sensiolabs.com/projects/9a345021-c8a1-4f48-948a-d15de51d9909)
[![Packagist](https://img.shields.io/packagist/v/phpro/grumphp.svg)](https://packagist.org/packages/phpro/grumphp)
[![Twitter](https://img.shields.io/badge/Twitter-%40grumphp-blue.svg)](https://twitter.com/intent/user?screen_name=grumphp)
[![Freenode](https://img.shields.io/badge/Freenode-%23grumphp-blue.svg)](http://webchat.freenode.net?channels=%23grumphp&uio=d4)

# GrumPHP

<img src="https://raw.githubusercontent.com/phpro/grumphp/master/resources/logo/grumphp-grumpy.png" align="right" width="250"/>

Sick and tired of defending code quality over and over again? GrumPHP will do it for you!
This composer plugin will register some git hooks in your package repository.
When somebody commits changes, GrumPHP will run some tests on the committed code.
If the tests fail, you won't be able to commit your changes.
This handy tool will not only improve your codebase, it will also learn your co-workers to write better code following the best practices you've determined as a team.

GrumPHP has a set of common tasks built-in. You will be able to use GrumPHP with a minimum of configuration.

We don't want to bore you with all the details, so quick: install it yourself and unleash the power of GrumPHP!

## Installation

*If you are trying to install GrumPHP on Windows: please read the windows pre-install section.*

This package is composer plugin and should be installed to your project's dev dependency using composer:

```sh
composer require phpro/grumphp
```

When the package is installed, GrumPHP will attach itself to the git hooks of your project.
You will see following message in the composer logs:

*Watch out! GrumPHP is sniffing your commits!*

To make GrumPHP even more awesome, it will suggest installing some extra packages:

- behat/behat : ~3.0
- fabpot/php-cs-fixer: ~1.10
- phpspec/phpspec : ~2.1
- phpunit/phpunit : ~4.5
- roave/security-advisories : dev-master@dev
- squizlabs/php_codesniffer : ~2.3
- codeception/codeception : ~2.1

GrumPHP will never push you into using a specific task. You can choose the tasks that fit your needs, and activate or
deactivate any task in no time!

### Windows Pre-Install
So you are running windows but still want to unleash the power of GrumPHP? No problem: everything is possible!
You will have to make sure that following items are available on the command line:

- php
- composer
- git


### Installation with an exotic project structure

When your application has a project structure that is not covered by the default configuration settings,
you will have to create a `grumphp.yml` *before* installing the package
and add next config into your application's `composer.json`:

```
# composer.json
"extra": {
    "grumphp": {
        "config-default-path": "path/to/grumphp.yml"
    }
}
```

You can also change the configuration after installation.
The only downfall is that you will have to initialize the git hook manually:

```sh
php ./vendor/bin/grumphp git:init --config=path/to/grumphp.yml
```

### Global installation

It is possible to install or update GrumPHP on your system with following commands:

```sh
composer global require phpro/grumphp
composer global update phpro/grumphp
```

This will install the `grumphp` executable in the `~/.composer/vendor/bin` folder.
Make sure to add this folder to your system `$PATH` variable:

```
# .zshrc or .bashrc
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

That's all! The `grumphp` command will be available on your CLI and will be used by default.

**Note:** that you might want to re-initialize your project git hooks to make sure the system-wide executable is being used. Run the `grumphp git:init` command in the project directory.

**Note:** When you globally installed 3rd party tools like e.g. `phpunit`, those will also be used instead of the composer executables.

## Build your own conventions checker

You can see an [example](https://github.com/linkorb/conventions-checker)
of how to build your own conventions checker.

## Configuration

Sample `grumphp.yml`:

```yaml
# grumphp.yml
parameters:
    bin_dir: "./vendor/bin"
    git_dir: "."
    ascii:
        failed: grumphp-grumpy.txt
        succeeded: grumphp-happy.txt
    tasks:
        behat: ~
        blacklist: ~
        git_commit_message: ~
        phpcsfixer: ~
        phpcs:
            standard: "PSR2"
        phpspec: ~
        phpunit: ~
        codeception: ~
```

### Set up basic configuration

GrumPHP comes shipped with a configuration tool. Run following command to create a configuration file:

```sh
php ./vendor/bin/grumphp configure
```

This command is also invoked during installation.
It wil not ask you for anything, but it will try to guess the best possible configuration.

### Parameters

**bin_dir**

*Default: ./bin/vendor*

This parameter will tell GrumPHP where it can locate external commands like phpcs and phpspec.
It defaults to the default composer bin directory.

**git_dir**

*Default: .*

This parameter will tell GrumPHP in which folder it can find the .git folder.
This parameter is used to create the git hooks at the correct location. It defaults to the working directory.

**ascii**

*Default: {failed: grumphp-grumpy.txt, succeeded: grumphp-happy.txt}*

This parameter will tell GrumPHP where it can locate ascii images used in pre-commit hook.
Currently there are only two images `failed` and `succeeded`. If path is not specified default image from
`resources/ascii/` folder are used.

```yaml
# grumphp.yml
parameters:
    ascii:
        failed: resource/grumphp-grumpy.txt
        succeeded: ~
```

To disable banner set ascii images path to `~`.

### Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

```yaml
# grumphp.yml
parameters:
    tasks:
        behat: ~
        blacklist: ~
        phpcsfixer: ~
        phpcs: ~
        phpspec: ~
        phpunit: ~
        codeception:
            suite: TestSuite
```

Every task has it's own default configuration. It is possible to overwrite the parameters per task.


#### Behat

The Behat task will run your Behat tests.
It lives under the `behat` namespace and has following configurable parameters:

**config**

*Default: null*

If you want to use a different config file than the default behat.yml, you can specify your custom config file location with this option.


**format**

*Default: null*

If you want to use a different formatter than the default one, specify it with this option.


**suite**

*Default: null*

If you want to run a particular suite only, specify it with this option.


**stop_on_failure**

*Default: false*

When this option is enabled, behat will stop at the first error. This means that it will not run your full test suite when an error occurs.

#### Blacklist

The Blacklist task will test your changes for blacklisted keywords, such as `die(`, `var_dump(` etc.
It lives under the `blacklist` namespace and has following configurable parameters:

**keywords**

*Default: null*

Use this parameter to specify your blacklisted keywords list.
For example:

```yaml
# grumphp.yml
parameters:
    tasks:
        blacklist:
            keywords:
                - "die("
                - "var_dump("
                - "exit;"
```

#### Git commit message (git_commit_message)

The git comit message can be used in combination with the git hook `git:commit-msg`.
It can be used to enforce patterns in a commit message.
For example: if you are working with JIRA, it is possible to add a pattern for the JIRA issue number.

**matchers**

*Default: []*

Use this parameter to specify one or multiple patterns. The value can be in regex or glob style.
Here are some example matchers:

- /JIRA-([0-9]*)/
- pre-fix*
- *suffix
- ...

**case_insensitive**

*Default: true*

Mark the matchers as case sensitive.

**multiline**

*Default:true*

Markt he matchers as multiline.



#### PHP-CS-Fixer

The PHP-CS-Fixer task will run codestyle checks.
It lives under the `phpcsfixer` namespace and has following configurable parameters:


**config_file**

*Default: null*

You can specify the path to the .php_cs file.


**config**

*Default: 'default'*

There such predefined configs for codestyle checks: `default`, `magento`, `sf23`.
If you want to run a particular config, specify it with this option.


**filters**

*Default: array()*

There are a lot of fixers which you can apply to your code. You can specify an array of them in this config.
The full list of fixers you can find [here](https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage).


**level**

*Default: ''*

Fixers are grouped by levels: `psr0`, `psr1`, `psr2` you can specify a group instead of applying them separately.


**verbose**

*Default: true*

Show applied fixers.


#### Phpcs

The Phpcs task will sniff your code for bad coding standards.
It lives under the `phpcs` namespace and has following configurable parameters:

**standard**

*Default: PSR2*

This parameter will describe which standard is being used to validate your code for bad coding standards.


**show_warnings**

*Default: true*

Triggers an error when there are warnings.


**tab_width**

*Default: null*

By default, the standard will specify the optimal tab-width of the code. If you want to overwrite this option, you can use this configuration option.


**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpcs. With this option you can skip files like tests. Leave this option blank to run phpcs for every php file.


**sniffs**

*Default: []*

This is a list of sniffs that need to be executed. Leave this option blank to run all configured sniffs for the selected standard.

##### PHPCS framework presets

**Symfony 2**

If you want to use Phpcs for your Symfony2 projects, you can require the leanpub phpcs repo.

```sh
composer require --dev leaphub/phpcs-symfony2-standard
```

Following this, you can add the path to your phpcs task.

```yml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: "vendor/leaphub/phpcs-symfony2-standard/leaphub/phpcs/Symfony2/"
```

**Magento**

If you want to use Phpcs for your Magento projects, you can require the magento-ecg repo.

```sh
composer require --dev magento-ecg/coding-standard
```

Following this, you can add the path to your phpcs task.

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcs:
            standard: "vendor/magento-ecg/coding-standard/Ecg/"
            show_warnings: false
```

#### Phpspec

The Phpspec task will spec your code with Phpspec.
It lives under the `phpspec` namespace and has following configurable parameters:

**config_file**

*Default: null*

If your phpspec.yml file is located at an exotic location, you can specify your custom config file location with this option.


**stop_on_failure**

*Default: false*

When this option is enabled, phpspec will stop at the first error. This means that it will not run your full test suite when an error occurs.


#### Phpunit

The Phpunit task will run your unit tests.
It lives under the `phpunit` namespace and has following configurable parameters:

**config_file**

*Default: null*

If your phpunit.xml file is located at an exotic location, you can specify your custom config file location with this option.
This option is set to `null` by default.
This means that `phpunit.xml` or `phpunit.xml.dist` are automatically loaded if one of them exist in the current directory.

#### Codeception
The Codeception task will run your full-stack tests. It live under the `codecept` namespace and has the following configurable parameters:

**config_file**

*Default: null*

If your `codeception.yml` file is located in an exotic location, you can specify your custom config file location with this option. This option is set to `null` by default. This means that `codeception.yml` is automatically located if it exists in the current directory.

**fail-fast**

*Default: false*

When this option is enabled, Codeception will stop at the first error. This means that it will not run your full test suite when an error occurs.

**suite**

*Default: null*

When this option is specified it will only run tests for the given suite. If left `null` Codeception will run tests for your full test suite.

**test**

*Default: null*

When this option is specified it will only run the given test. If left `null` Codeception will run all tests within the suite.

#### Custom tasks

It is very easy to configure your own project specific task.
You just have to create a class that implements the `GrumPHP\Task\TaskInterface`.
Next register it to the service manager and add your task configuration:

```yaml
# grumphp.yml
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

**Note:** You do NOT have to add the main and task configuration. This example just shows you how to do it.
You're welcome!

You just registered your custom task in no time! Pretty cool right?!

## Events

It is possible to hook in to GrumPHP with events.
Internally the Symfony event dispatcher is being used. 
This means it can be configured just like you would in Symfony: 

```yml
# grumphp.yml
services:
    listener.some_listener:
        class: MyNamespace\EventListener\MyListener
        tags:
            - { name: grumphp.event_listener, event: grumphp.runner.run }
            - { name: grumphp.event_listener, event: grumphp.runner.run, method: customMethod, priority: 10 }
    listener.some_subscriber:
        class: MyNamespace\EventSubscriber\MySubscriber
        tags:
            - { name: grumphp.event_subscriber }
```

Following events are triggered during execution:

| Event name              | Event class       | Triggered
| ----------------------- | ----------------- | ----------
| grumphp.task.run        | TaskEvent         | before a task is executed
| grumphp.task.failed     | TaskFailedEvent   | when a task fails
| grumphp.task.complete   | TaskEvent         | when a task succeeds
| grumphp.runner.run      | RunnerEvent       | before the tasks are executed
| grumphp.runner.failed   | RunnerFailedEvent | when one task failed
| grumphp.runner.complete | RunnerEvent       | when all tasks succeed


## Roadmap

Following tasks are still on the roadmap:

- composer validate command
- phpmd
- phpcpd
- phpdcd
- twig lint
- json lint
- yaml lint
- xml lint / dtd validation
- grunt tests
- gulp tests
- npm test tests
- ...

New features or bugfixes can be logged at the [https://github.com/phpro/grumphp/issues](issues list).
Want to help out? Feel free to contact us!


# Execution

GrumPHP will be triggered with GIT hooks. However, you can execute the trigger at the command line:


```sh
php ./vendor/bin/grumphp git:pre-commit
php ./vendor/bin/grumphp git:commit-msg
```

If you want to run the tests on the full codebase, you can run the command:
```sh
php ./vendor/bin/grumphp run
```

# Compatibility

This package has been tested with following git clients:

- CLI unix
- CLI Mac
- CLI Windows
- Phpstorm GIT
- Atlassian SourceTree
- Syntevo SmartGit

# Solving issues

## GrumPHP does not work with submodules.
When you use a submodule, GrumPHP will throw diff errors.
This is because the plugin uses Gitlib which does not support submodules.

If you do not need to update your submodule, you can just remove all references to it.
If the changed .gitmodules is not commited nothing will change in your repo.

Ref.: https://github.com/gitonomy/gitlib/issues/12

