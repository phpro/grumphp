[![Travis](https://img.shields.io/travis/phpro/grumphp/master.svg)](http://travis-ci.org/phpro/grumphp)
[![Insight](https://img.shields.io/sensiolabs/i/9a345021-c8a1-4f48-948a-d15de51d9909.svg)](https://insight.sensiolabs.com/projects/9a345021-c8a1-4f48-948a-d15de51d9909)
[![AppVeyor](https://ci.appveyor.com/api/projects/status/ttlbau2sjg36ep01/branch/master?svg=true)](https://ci.appveyor.com/project/veewee/grumphp/branch/master)
[![Installs](https://img.shields.io/packagist/dt/phpro/grumphp.svg)](https://packagist.org/packages/phpro/grumphp/stats)
[![Packagist](https://img.shields.io/packagist/v/phpro/grumphp.svg)](https://packagist.org/packages/phpro/grumphp)

[![Twitter](https://img.shields.io/badge/Twitter-%40grumphp-blue.svg)](https://twitter.com/intent/user?screen_name=grumphp)
[![Freenode](https://img.shields.io/badge/Freenode-%23grumphp-blue.svg)](http://webchat.freenode.net?channels=%23grumphp&uio=d4)
[![Join the chat at https://gitter.im/phpro/grumphp](https://badges.gitter.im/phpro/grumphp.svg)](https://gitter.im/phpro/grumphp?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

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

GrumPHP will never push you into using a specific task. You can choose the tasks that fit your needs, and activate or
deactivate any task in no time! See the `suggest` section in [composer.json](https://github.com/phpro/grumphp/blob/master/composer.json#L37).

Note: GrumPHP will overwrite existing hooks unless you run `composer install` with `--no-plugins` or `--no-scripts`. Be sure to backup your hooks before you try to install GrumPHP.

Having trouble installing GrumPHP? Find out how to:

- [Install globally](doc/installation/global.md)
- [Install with an exotic project structure](doc/installation/exotic.md)
- [Install on Windows](doc/installation/windows.md)

## Configuration

Some things in GrumPHP can be configured in a `grumphp.yml` or `grumphp.yml.dist` file in the root of your project (the directory where you run the grumphp command).
You can specify a custom config filename and location in `composer.json` or in the `--config` option of the console commands.

```yaml
# grumphp.yml
parameters:
    bin_dir: "./vendor/bin"
    git_dir: "."
    hooks_dir: ~
    hooks_preset: local
    stop_on_failure: false
    ignore_unstaged_changes: false
    hide_circumvention_tip: false
    process_async_limit: 10
    process_async_wait: 1000
    process_timeout: 60
    ascii:
        failed: grumphp-grumpy.txt
        succeeded: grumphp-happy.txt
    tasks:
        ant: ~
        atoum: ~
        behat: ~
        brunch: ~
        clover_coverage: ~
        codeception: ~
        composer: ~
        composer_require_checker: ~
        composer_script: ~
        deptrac: ~
        doctrine_orm: ~
        file_size: ~
        gherkin: ~
        git_blacklist: ~
        git_branch_name: ~
        git_commit_message: ~
        grunt: ~
        gulp: ~
        infection: ~
        jsonlint: ~
        kahlan: ~
        make: ~
        npm_script: ~
        phan: ~        
        phing: ~
        php7cc: ~
        phpcpd: ~
        phpcs: ~
        phpcsfixer: ~
        phpcsfixer2: ~
        phplint: ~
        phpmd: ~
        phpmnd: ~
        phpparser: ~
        phpspec: ~
        phpstan: ~
        phpunit: ~
        phpunitbridge: ~
        phpversion: ~
        progpilot: ~
        psalm: ~
        robo: ~
        securitychecker: ~
        shell: ~
        xmllint: ~
        yamllint: ~
    testsuites: []
    extensions: []
```

Details of the configuration are broken down into the following sections.

- [Parameters](doc/parameters.md) &ndash; Configuration settings for GrumPHP itself.
- [Tasks](doc/tasks.md) &ndash; External tasks performing code validation and their respective configurations.
- [TestSuites](doc/testsuites.md)
- [Extensions](doc/extensions.md)
- [Events](doc/events.md)
- [Conventions checker](doc/conventions.md)

## Commands

Since GrumPHP is just a CLI tool, these commands can be triggered:

- [configure](doc/commands.md#installation)
- [git:init](doc/commands.md#installation)
- [git:deinit](doc/commands.md#installation)
- [git:pre-commit](doc/commands.md#git-hooks)
- [git:commit-msg](doc/commands.md#git-hooks)
- [run](doc/commands.md#run)

## Compatibility

GrumPHP works with PHP 5.6 or above.

This package has been tested with following git clients:

- CLI Unix
- CLI Mac
- CLI Windows
- PhpStorm Git
- Atlassian SourceTree
- Syntevo SmartGit

## Roadmap

Lots of tasks are already available to make sure your team writes great code.
There is one major part missing before we can release v1.0.0:

- [A PHAR executable](https://github.com/phpro//grumphp/issues/61)

We are always looking to support new tasks.
Feel free to log an issue or create a pull request for a task we forgot.

Are you missing a feature or did you find a bug?
Log it in the [issue tracker](https://github.com/phpro/grumphp/issues)!
Want to help out? Feel free to contact us!

## Solving issues

- [GrumPHP does not work with submodules](doc/issues/grumphp-is-not-working-with-submodules.md)

## FAQ
- [FAQ](doc/faq.md)

## About

### Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/phpro/grumphp/issues).
Please take a look at our rules before [contributing your code](CONTRIBUTING.md).

### License

GrumPHP is licensed under the [MIT License](LICENSE).
