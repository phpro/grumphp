# Conventions

Are you building multiple applications or packages and do you want to make sure the same rules are validated on all those repositories?
No problem! Since we are using the Symfony Container, your configuration can be very flexible.
In this chapter we will discuss some ways to create your own convention checker package.

## Hardcoded conventions

If you want to use one `grumphp.yml` file which cannot be customized by your project, this is the way to configure your application:

```sh
composer require --dev [your-project]/[your-convention-package]
```

Next you'll have to change the project's `composer.json` file: 

```json
{
    "extra": {
        "grumphp": {
            "config-default-path": "vendor/[your-project]/[your-convention-package]/[some-dir]/grumphp.yml"
        }
    }
}
```

You can see an [example](https://github.com/linkorb/conventions-checker)
of how to build your own conventions checker.


## Customizable conventions

Customizable conventions are very flexible! The idea is that you define a `grumphp.yml` file which is filled with custom parameters.
Next, you can import a general `grumphp.yml` file from another vendor directory inside the project's config file.
The configuration will look like this:


```sh
composer require --dev [your-project]/[your-convention-package]
```

Sample conventions grumphp file:

```yml
# Convention grumphp.yml
parameters:
  convention.git_commit_message_matchers: ['/.*/']
grumphp:
  tasks:
    phpunit: ~
    git_commit_message:
      matchers: "%convention.git_commit_message_matchers%"
      case_insensitive: false
      multiline: false
```

Sample project grumphp file:

```yml
# Project grumphp.yml
imports:
    - { resource: vendor/[your-project]/[your-convention-package]/grumphp.yml }
parameters:
    convention.git_commit_message_matchers: ['/^JIRA-\d+: [A-Z].+\./']
```

This way, you can define some common rules, but make it possible to customize some project specific settings.
