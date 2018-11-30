# Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

```yaml
# grumphp.yml
parameters:
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
```

Every task has it's own default configuration. It is possible to overwrite the parameters per task.

## Tasks

- [Ant](tasks/ant.md)
- [Atoum](tasks/atoum.md)
- [Behat](tasks/behat.md)
- [Brunch](tasks/brunch.md)
- [Clover Coverage](tasks/clover_coverage.md)
- [Codeception](tasks/codeception.md)
- [Composer](tasks/composer.md)
- [Composer Require Checker](tasks/composer_require_checker.md)
- [Composer Script](tasks/composer_script.md)
- [Doctrine ORM](tasks/doctrine_orm.md)
- [File size](tasks/file_size.md)
- [Deptrac](tasks/deptrac.md)
- [Gherkin](tasks/gherkin.md)
- [Git blacklist](tasks/git_blacklist.md)
- [Git branch name](tasks/git_branch_name.md)
- [Git commit message](tasks/git_commit_message.md)
- [Grunt](tasks/grunt.md)
- [Gulp](tasks/gulp.md)
- [Infection](tasks/infection.md)
- [JsonLint](tasks/jsonlint.md)
- [Kahlan](tasks/kahlan.md)
- [Make](tasks/make.md)
- [NPM script](tasks/npm_script.md)
- [Phan](tasks/phan.md)
- [Phing](tasks/phing.md)
- [Php7cc](tasks/php7cc.md)
- [PhpCpd](tasks/phpcpd.md)
- [Phpcs](tasks/phpcs.md)
- [PHP-CS-Fixer](tasks/phpcsfixer.md)
- [PHP-CS-Fixer 2](tasks/phpcsfixer2.md)
- [PHPLint](tasks/phplint.md)
- [PhpMd](tasks/phpmd.md)
- [PhpMnd](tasks/phpmnd.md)
- [PhpParser](tasks/phpparser.md)
- [Phpspec](tasks/phpspec.md)
- [PHPStan](tasks/phpstan.md)
- [Phpunit](tasks/phpunit.md)
- [Phpunit bridge](tasks/phpunitbridge.md)
- [PhpVersion](tasks/phpversion.md)
- [Progpilot](tasks/progpilot.md)
- [Psalm](tasks/psalm.md)
- [Robo](tasks/robo.md)
- [Security Checker](tasks/securitychecker.md)
- [Shell](tasks/shell.md)
- [XmlLint](tasks/xmllint.md)
- [YamlLint](tasks/yamllint.md)

## Metadata

Every task has a pre-defined `metadata` key on which application specific options can be configured.
For example:

```yaml
# grumphp.yml
parameters:
    tasks:
        anytask:
            metadata:
                blocking: true
                priority: 0
```

**priority**

*Default: 0*

This option can be used to specify the order in which the tasks will be executed.
The higher the priority, the sooner the task will be executed.


**blocking**

*Default: true*

This option can be used to make a failing task non-blocking.
By default all tasks will be marked as blocking.
When a task is non-blocking, the errors will be displayed but the tests will pass.


## Creating a custom task

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
          - '@config'
        tags:
          - {name: grumphp.task, config: myConfigKey}
```

**Note:** You do NOT have to add the main and task configuration. This example just shows you how to do it.
You're welcome!

You just registered your custom task in no time! Pretty cool right?!


## Run the same task twice with different configuration

In some cases you might want to run the same task but with different configuration.
The suggested way of doing this, is by registering the existing task with a different name.
Configuration of the additional task will look like this:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpcsfixer2:
            allow_risky: true
            path_mode: intersection
        phpcsfixer2_typo3:
            allow_risky: true
            config: .typo3.php_cs
            path_mode: intersection

services:
    task.phpcsfixer2_typo3:
        class: Acme\Typo3\ConventionsChecker\Task\PhpCsFixerV2Typo3
        arguments:
            - '@config'
            - '@process_builder'
            - '@async_process_runner'
            - '@formatter.phpcsfixer'
        tags:
            - {name: grumphp.task, config: phpcsfixer2_typo3}            
```

Since we currently match the name based on the task name, you'll also have to create a new task class:

````php
<?php
// Acme/Typo3/ConventionsChecker/Task/PhpCsFixerV2Typo3.php

namespace Acme\Typo3\ConventionsChecker\Task;

use GrumPHP\Task\PhpCsFixerV2;

class PhpCsFixerV2Typo3 extends PhpCsFixerV2
{
    public function getName()
    {
        return 'phpcsfixer2_typo3';
    }
}
````

