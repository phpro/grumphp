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
This handy tool will not only improve your codebase, it will also teach your co-workers to write better code following the best practices you've determined as a team.

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
        git_blacklist: ~
        git_commit_message: ~
        phpcsfixer: ~
        phpcs:
            standard: "PSR2"
        phpspec: ~
        phpunit: ~
        codeception: ~
    extensions: []
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
        codeception: ~
        behat: ~
        git_blacklist: ~
        git_commit_message: ~
        phpcsfixer: ~
        phpcs: ~
        phpspec: ~
        phpunit: ~
```

Every task has it's own default configuration. It is possible to overwrite the parameters per task.

**Tasks**
- [Behat](doc/tasks/behat.md)
- [Git blacklist](doc/tasks/git_blacklist.md)
- [Git commit message](doc/tasks/git_commit_message.md)
- [Grunt](doc/tasks/grunt.md)
- [PHP-CS-Fixer](doc/tasks/php_cs_fixer.md)
- [PHP-CS-Fixer](doc/tasks/php_cs_fixer.md)
- [Phpcs](doc/tasks/phpcs.md)
- [Phpspec](doc/tasks/phpspec.md)
- [Phpunit](doc/tasks/phpunit.md)
- [Codeception](doc/tasks/codeception.md)

**Creating a custom  task**
It is also very easy to configure your own [Custom tasks](doc/tasks/custom_tasks.md).

## Events

It is possible to hook in to GrumPHP with events.
Internally the Symfony event dispatcher is being used. 
[List of available events](docs/events.md)



## Extensions

You will probably have some custom tasks or event listeners that are not included in the default GrumPHP project.
It is possible to group this additional GrumPHP configuration in an extension. 
This way you can easily create your own extension package and load it whenever you need it.

[Create your own extension](docs/extensions.md)

## Roadmap

Following tasks are still on the roadmap:

- composer validate command
- phpmd
- phpcpd
- phpdcd
- robo
- twig lint
- symfony validation
- json lint
- yaml lint
- xml lint / dtd validation
- gulp tests
- npm test tests
- ...

New features or bugfixes can be logged at the [https://github.com/phpro/grumphp/issues](issues list).
Want to help out? Feel free to contact us!


# Execution

GrumPHP will be triggered with GIT hooks. [However, you can execute the trigger at the command line](docs/execution.md)

# Compatibility

This package has been tested with following git clients:

- CLI Unix
- CLI Mac
- CLI Windows
- PhpStorm Git
- Atlassian SourceTree
- Syntevo SmartGit

# Solving issues

## GrumPHP does not work with submodules.
When you use a submodule, GrumPHP will throw diff errors.
This is because the plugin uses Gitlib which does not support submodules.

If you do not need to update your submodule, you can just remove all references to it.
If the changed .gitmodules is not commited nothing will change in your repo.

Ref.: https://github.com/gitonomy/gitlib/issues/12

