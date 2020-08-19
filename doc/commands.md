# Commands

## Installation
GrumPHP comes shipped with a configuration tool. Run following command to create a basic configuration file:

```sh
php ./vendor/bin/grumphp configure
```

This command gets invoked during installation.
It wil not ask you for anything, but it will try to guess the best possible configuration.

To enable or disable GrumPHP, you can run one of the following commands:
 
```sh
php ./vendor/bin/grumphp git:init
php ./vendor/bin/grumphp git:deinit
```

The `git:init` is triggered by the composer plugin during installation.

## Git hooks

GrumPHP will be triggered with GIT hooks. However, you can run following commands:

```sh
php ./vendor/bin/grumphp git:pre-commit
php ./vendor/bin/grumphp git:commit-msg
```

Both commands support raw git diffs and file lists as STDIN input. 
This way it is possible to pass changes triggered by `git commit -a` from the GIT hook to the GrumPHP commands.
If no stdin is provided, it will load the currently staged git diff.

Example stdin usages:

```sh
git diff | php ./vendor/bin/grumphp git:pre-commit
git diff --staged | php ./vendor/bin/grumphp git:pre-commit
git ls-files src | php ./vendor/bin/grumphp git:pre-commit
```

:exclamation: *If you use the stdin, we won't be able to answer questions interactively.*

## Run

If you want to run the tests on the full codebase, you can run the command:

```sh
php ./vendor/bin/grumphp run
php ./vendor/bin/grumphp run --testsuite=mytestsuite
```

This command can also be used for continious integration.
More information about the testsuites can be found in the [testsuites documentation](testsuites.md).

If you want to run only a subset of the configured tasks, you can run the command with the `--tasks` option:

```sh
php ./vendor/bin/grumphp run --tasks=task1,task2
```

The `--tasks` value has to be a comma-separated string of task names that match the keys in the `tasks` section 
of the `grumphp.yml` file. See [#580](https://github.com/phpro/grumphp/issues/580) for a more exhaustive explanation.

The run command support raw git diffs and file lists as STDIN input. 
This way it is possible to select which files you want grumphp to check.
If no stdin is provided, it will load all files known to git.

Example stdin usages:

```sh
git diff | php ./vendor/bin/grumphp run
git diff --staged | php ./vendor/bin/grumphp run
git ls-files src | php ./vendor/bin/grumphp run
```

:exclamation: *If you use the stdin, we won't be able to answer questions interactively.*
 