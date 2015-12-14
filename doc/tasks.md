# Tasks
It is easy to configure and activate tasks in GrumPHP.
Tasks live under their own namespace in the parameters part.
To activate a task, it is sufficient to add an empty task configuration:

```yaml
# grumphp.yml
parameters:
    tasks:
        behat: ~
        codeception: ~
        composer: ~
        git_blacklist: ~
        git_commit_message: ~
        grunt: ~
        phpcs: ~
        phpcsfixer: ~
        phpspec: ~
        phpunit: ~
```

Every task has it's own default configuration. It is possible to overwrite the parameters per task.

## Tasks

- [Behat](tasks/behat.md)
- [Codeception](tasks/codeception.md)
- [Composer](tasks/composer.md)
- [Git blacklist](tasks/git_blacklist.md)
- [Git commit message](tasks/git_commit_message.md)
- [Grunt](tasks/grunt.md)
- [Phpcs](tasks/phpcs.md)
- [PHP-CS-Fixer](tasks/php_cs_fixer.md)
- [Phpspec](tasks/phpspec.md)
- [Phpunit](tasks/phpunit.md)

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
