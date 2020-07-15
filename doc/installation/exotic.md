# Installation with an exotic project structure

By default, GrumPHP` is trying to auto-discover a lot of possible project structures.
If for some reason GrumPHP is not able to find everything, you can help it out.
Here are some possible solutions:


## Register paths in your composer file

**High priority**: Composer settings will be used when no environment variables are set.

You can manipulate how GrumPHP works based on settings in your composer file:


```json
# composer.json
{
    "extra": {
        "grumphp": {
            "config-default-path": "path/to/grumphp.yml",
            "project-path": "path/to/your/project/folder",
            "disable-plugin": false
        }
    }
}
```
**config-default-path**

*Default: null*

The path to your Grumphp configuration file.


**project-path**

*Default: null*

The path to the root of your project. This must be a subdirectory of your git working directory.


When your application has a project structure that is not covered by the default configuration settings,
you will have to create a `grumphp.yml` *before* installing the package
and add next config into your application's `composer.json`:

All parameters above are optional.
After changing a parameter, you might want to re-initialize your git hooks:

```sh
php ./vendor/bin/grumphp git:init
```

**disable-plugin**

*Default: false*

In some cases, you don't want composer to initialise GrumPHP automatically.
For example: on global installations activating the plugin is questionable.
Luckily you can opt-out on this behaviour.


## Changing paths by using environment variables

**Highest priority**: Environment variables will always be used when available.

You can set one or multiple of the environment variables below.
Paths can be absolute or relative against current working directory.
You can also set these variables directly in your git hook. However, this might require a custom hook template.

```sh
GRUMPHP_PROJECT_DIR=...
GRUMPHP_GIT_WORKING_DIR=....
GRUMPHP_GIT_REPOSITORY_DIR=...
GRUMPHP_COMPOSER_DIR=...
GRUMPHP_BIN_DIR=...
PATH=/additional/bin/dirs:$PATH
```

**GRUMPHP_PROJECT_DIR**

*Default: null*

The path to the root of your project. This must be a subdirectory of your git working directory.

**GRUMPHP_GIT_WORKING_DIR**

*Default: null*

The path to the root directory of your git project.

**GRUMPHP_GIT_REPOSITORY_DIR**

*Default: null*

The path to the directory in which git stores it objects etc.
Most of the time this is the .git folder.
When using GIT submodules, this is the location of the submodule .git folder.

**GRUMPHP_COMPOSER_DIR**

*Default: null*

The directory in which your `composer.json` file is available.

**GRUMPHP_BIN_DIR**

*Default: null*

The directory in which the executables installed by composer are located.

**PATH**

*Default: system specific*

If you want to support multiple bin directories, you can prepend them to your path variable.


### Environment variables

It is also possible to set some of the environment variables above inside the `grumphp.yaml` file directly:

```yaml
grumphp:
  environment:
    variables:
      GRUMPHP_PROJECT_DIR: "..."
      GRUMPHP_GIT_WORKING_DIR: "..."
      GRUMPHP_GIT_REPOSITORY_DIR: "..."
      GRUMPHP_BIN_DIR: "..."
    paths:
      - 'tools' 
```

The configuration from inside the `grumphp.yaml` file will be loaded if the guessing system was able to determine an initial version of the guessed paths.
This is required because GrumPHP tries to guess its config file based on all the parameters above.
Once the config is loaded, it does a second guess based on environment variables that were detected inside the `grumphp.yaml` file.


## Running GrumPHP with a custom config file

**Highest priority**: When adding a config CLI attribute, this will always be used.

In some situations, you might want to use a different GrumPHP configuration file.
You can switch configuration by using the `--config` parameter that is available on the CLI.

```sh
php ./vendor/bin/grumphp git:init --config='custom/grumphp.yml'
php ./vendor/bin/grumphp run --config='custom/grumphp.yml'
```
