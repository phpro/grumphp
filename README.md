[![Build status](https://api.travis-ci.org/phpro/grumphp.svg)](http://travis-ci.org/phpro/grumphp)

# GrumPHP

Sick and tired of complaining about your co-workers' code? GrumPHP will do it for you!
This composer plugin will register some git hooks in your package repository.
When somebody commits changes, GrumPHP will run some tests on the committed code.
If the tests fail, you won't be able to commit your changes.
This handy tool will make your co-workers care about their code.

GrumPHP has a set of common tasks built-in. You will be able to use GrumPHP with a minimum of configuration.

We don't want to bore you with all the details, so quick: install it yourself and unleash the power of GrumPHP!

## Installation

*If you are trying to install GrumPHP on Windows: please read the windows pre-install section.*

This package is composer plugin and should be installed to your project's dev dependency using composer:

```sh
composer self-update
composer require --dev phpro/grumphp:~0.1
```

When the package is installed, GrumPHP will attach itself to the git hooks of your project. 
You will see following message in the composer logs:

*Watch out! GrumPHP is sniffing your commits!*

To make GrumPHP even more awesome, it will suggest installing some extra packages:

- squizlabs/php_codesniffer : ~2.3
- phpspec/phpspec : ~2.1
- phpunit/phpunit : ~4.5
- roave/security-advisories : dev-master@dev

GrumPHP will never push you into using a specific task. You can choose the tasks that fit your needs, and activate or 
deactivate any task in no time!

### Windows Pre-Install
So you are running windows but still want to unleash the power of GrumPHP? No problem: everything is possible!
You will have to make sure that following items are available on the command line:

- php
- composer
- git


### Installation with an exotic project structure:
When your application has a project structure that is not covered by the default configuration settings, 
you will have to create a `grumphp.yml` file at the same location as `composer.json` *before* installing the package.

It is possible to place your configuration file at another location or change the configuration after installation.
The only downfall is that you will have to initialize the git hook manually:

```sh
php ./vendor/bin git:init --config=path/to/grumphp.yml
```

## Configuration

Sample `grumphp.yml`:

``` yaml
# grumphp.yml
parameters:
    bin_dir: "./vendor/bin"
    git_dir: "."
    ascii:
        failed: failed.txt
        succeeded: succeeded.txt
    tasks:
        behat: ~
        phpcsfixer: ~
        phpcs:
            standard: "PSR2"
        phpspec: ~
        phpunit: ~
```

### Set up basic configuration
GrumPHP comes shipped with a configuration tool. Run following command to create a configuration file:
```
php ./vendor/bin/grumphp configure
```

This command is also invoked during installation. 
It wil not ask you for anything, but it will try to guess the best possible configuration.

### Auto config via composer

* Install GrumPHP as a composer dependency
* Add `grumphp.yml` in your project
* Add setup script to composer.json in `post-install-cmd` section

```
"scripts": {
    "post-install-cmd": [
        "php ./vendor/bin/grumphp git:init"
    ]
}
```

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

*Default: {failed: failed.txt, succeeded: succeeded.txt}*

This parameter will tell GrumPHP where it can locate ascii images used in pre-commit hook.
Currently there are only two images `failed` and `succeeded`. If path is not specified default image from 
`resources/ascii/` folder are used.
```
ascii:
  failed: resource/failed.txt
  succeeded: ~
```
To disable banner set ascii images path to `~`.

### Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

``` yaml
# grumphp.yml
parameters:
    tasks:
        behat: ~
        phpcsfixer: ~
        phpcs: ~
        phpspec: ~
        phpunit: ~
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

*Default: phpunit.xml*

If your phpunit.xml file is located at an exotic location, you can specify your custom config file location with this option.


#### Custom tasks

It is very easy to configure your own project specific task. 
You just have to create a class that implements the `GrumPHP\Task\TaskInterface`.
Next register it to the service manager and add your task configuration:

``` yaml
# resources/config/services.yml
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

## Roadmap

Following tasks are still on the roadmap:

- composer.json and composer.lock need to be committed at the same time
- phpmd
- phpcpd
- phpdcd
- ...

In a future version, it will also be possible to use GrumPHP as a Continious Integration tool.

# Execution

GrumPHP will be triggered with GIT hooks. However, you can execute the trigger at the command line:


```sh
php ./vendor/bin git:pre-commit
```


# Compatibility

This package has been tested with following git clients:

- CLI unix
- CLI Windows
- Phpstorm GIT
- Atlassian SourceTree
- Syntevo SmartGit
