[![Build status](https://api.travis-ci.org/phpro/grumphp.svg)](http://travis-ci.org/phpro/grumphp)
[![Insight](https://img.shields.io/sensiolabs/i/9a345021-c8a1-4f48-948a-d15de51d9909.svg)](https://insight.sensiolabs.com/projects/9a345021-c8a1-4f48-948a-d15de51d9909)
[![AppVeyor](https://img.shields.io/appveyor/ci/veewee/grumphp.svg)](https://ci.appveyor.com/project/veewee/grumphp)
[![Installs](https://img.shields.io/packagist/dt/phpro/grumphp.svg)](https://packagist.org/packages/phpro/grumphp/stats)
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

## Demo
<img src="https://github.com/phpro/grumphp/wiki/images/demo.gif" alt="demo" width="100%" />

## Installation

*If you are trying to install GrumPHP on Windows: please read the windows pre-install section.*

This package is a composer plugin and should be installed to your project's dev dependency using composer:

```sh
composer require --dev phpro/grumphp
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
- sensiolabs/security-checker : ~3.0

GrumPHP will never push you into using a specific task. You can choose the tasks that fit your needs, and activate or
deactivate any task in no time!

Having trouble installing GrumPHP? Find out how to:

- [Install globally](doc/installation/global.md)
- [Install with an exotic project structure](doc/installation/exotic.md)
- [Install on Windows](doc/installation/windows.md)

## Configuration

```yaml
# grumphp.yml
parameters:
    bin_dir: "./vendor/bin"
    git_dir: "."
    stop_on_failure: false
    ignore_unstaged_changes: true
    ascii:
        failed: grumphp-grumpy.txt
        succeeded: grumphp-happy.txt
    tasks:
        behat: ~
        codeception: ~
        composer: ~
        git_blacklist: ~
        git_commit_message: ~
        grunt: ~
        jsonlint: ~
        phpcs: ~
        phpcsfixer: ~
        phpspec: ~
        phpunit: ~
        securitychecker: ~
        xmllint: ~
        yamllint: ~
    extensions: []
```

You can find a detailed overview of the configurable options in these sections:

- [Parameters](doc/parameters.md)
- [Tasks](doc/tasks.md)
- [Events](doc/events.md)
- [Extensions](doc/extensions.md)

## Commands

Since GrumPHP is just a CLI tool, these commands can be triggered:

- [configure](doc/commands.md#installation)
- [git:init](doc/commands.md#installation)
- [git:deinit](doc/commands.md#installation)
- [git:pre-commit](doc/commands.md#git-hooks)
- [git:commit-msg](doc/commands.md#git-hooks)
- [run](doc/commands.md#run)

## Compatibility

GrumPHP works with PHP 5.3 or above, and is also tested to work with HHVM.

This package has been tested with following git clients:

- CLI Unix
- CLI Mac
- CLI Windows
- PhpStorm Git
- Atlassian SourceTree
- Syntevo SmartGit

## Roadmap

Following tasks are still on the roadmap:

- phpmd
- phpcpd
- phpdcd
- robo
- twig lint
- symfony validation
- gulp tests
- npm tests
- humbug
- phing
- ant
- ...

New features or bugfixes can be logged at the [issue tracker](https://github.com/phpro/grumphp/issues).
Want to help out? Feel free to contact us!

## Build your own conventions checker

You can see an [example](https://github.com/linkorb/conventions-checker)
of how to build your own conventions checker.

## Solving issues

- [GrumPHP does not work with submodules](doc/issues/grumphp-is-not-working-with-submodules.md)

## About

### Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/phpro/grumphp/issues).
Please take a look at our rules before [contributing your code](CONTRIBUTING.md).

### License

GrumPHP is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
