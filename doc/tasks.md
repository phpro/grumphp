# Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

```yaml
# grumphp.yml
grumphp:
    tasks:
        ant: ~
        atoum: ~
        behat: ~
        brunch: ~
        clover_coverage: ~
        codeception: ~
        composer: ~
        composer_normalize: ~
        composer_require_checker: ~
        composer_script: ~
        deptrac: ~
        doctrine_orm: ~
        ecs: ~
        eslint: ~
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
        paratest: ~
        pest: ~
        phan: ~        
        phing: ~
        php7cc: ~
        phpcpd: ~
        phpcs: ~
        phpcsfixer: ~
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
        tester: ~
        twigcs: ~
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
- [Composer Normalize](tasks/composer_normalize.md)
- [Composer Require Checker](tasks/composer_require_checker.md)
- [Composer Script](tasks/composer_script.md)
- [Doctrine ORM](tasks/doctrine_orm.md)
- [Ecs EasyCodingStandard](tasks/ecs.md)
- [ESLint](tasks/eslint.md)
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
- [Paratest](tasks/paratest.md)
- [Pest](tasks/pest.md)
- [Phan](tasks/phan.md)
- [Phing](tasks/phing.md)
- [Php7cc](tasks/php7cc.md)
- [PhpCpd](tasks/phpcpd.md)
- [Phpcs](tasks/phpcs.md)
- [PHP-CS-Fixer](tasks/phpcsfixer.md)
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
- [Tester](tasks/tester.md)
- [TwigCs](tasks/twigcs.md)
- [XmlLint](tasks/xmllint.md)
- [YamlLint](tasks/yamllint.md)

## Metadata

Every task has a pre-defined `metadata` key on which application specific options can be configured.
For example:

```yaml
# grumphp.yml
grumphp:
    tasks:
        anytask:
            metadata:
                blocking: true
                label: null
                priority: 0
                task: null
```

**blocking**

*Default: true*

This option can be used to make a failing task non-blocking.
By default all tasks will be marked as blocking.
When a task is non-blocking, the errors will be displayed but the tests will pass.

**label**

*Default: null*

This option can be used to display a label instead of the task name whilst running GrumPHP.
By default the task name will be displayed.

**priority**

*Default: 0*

This option can be used to specify the order in which the tasks will be executed.
The higher the priority, the sooner the task will be executed.
All tasks with the same priority will run in parallel if parallel execution is enabled.

**task**

*Default: null*

This option can be used to specify which task you want to run.
This way you can configure the same task twice by using an alias with different options.
([For more information see below.](#run-the-same-task-twice-with-different-configuration))

## Creating a custom task

Creating a custom task is a matter of implementing the provided `GrumPHP\Task\TaskInterface`.
When your task is written, you have to register it to the service manager and add your task configuration to `grumphp.yaml`:

```php
<?php

interface TaskInterface
{
    public static function getConfigurableOptions(): OptionsResolver;
    public function canRunInContext(ContextInterface $context): bool;
    public function run(ContextInterface $context): TaskResultInterface;
    public function getConfig(): TaskConfigInterface;
    public function withConfig(TaskConfigInterface $config): TaskInterface;
}
```

* `getConfigurableOptions`: This method has to return all configurable options for the task.
* `canRunInContext`: Tells GrumPHP if it can run in `pre-commit`, `commit-msg` or `run` context.
* `run`: Executes the task and returns a result
* `getConfig`: Provides the resolved configuration for the task or an empty config for newly instantiated tasks.
* `withConfig`: Is used by GrumPHP to inject configuration during runtime. It should be immutable by default.

```yaml
# grumphp.yml
grumphp:
    tasks:
        myConfigKey:
            config1: config-value

services:
    My\Custom\Task:
        arguments:
          - '@some.required.dependency'
        tags:
          - {name: grumphp.task, task: defaultTaskName, priority: 0}
```

You now registered your custom task! Pretty cool right?!


## Testing your custom task.

We provided some base phpunit classes which you can use to test your tasks.
For a more detailed view on how to use these classes, you can scroll through our own unit tests section.

* `GrumPHP\Test\Task\AbstractTaskTestCase` : For testing basic tasks that don't trigger an external command.
* `GrumPHP\Test\Task\AbstractExternalTaskTestCase` : For testing tasks that trigger external commands.


## Run the same task twice with different configuration

In some cases you might want to run the same task but with different configuration.
Good news: This is perfectly possible!
You can use any name you want for the task, as long as you configure an existing task in the metadata section. 
Configuration of the additional task will look like this:

```yaml
# grumphp.yml
grumphp:
    tasks:
        phpcsfixer2:
            allow_risky: true
            path_mode: intersection
        phpcsfixer2_typo3:
            allow_risky: true
            config: .typo3.php_cs
            path_mode: intersection       
            metadata:
                task: phpcsfixer2
```
