# Composer

When the `composer.json` file has changed, the new file should be checked for issues.
This task will execute [`composer validate`](https://getcomposer.org/doc/03-cli.md#validate) to make sure that everything is OK.
The configuration looks like:

```yaml
# grumphp.yml
grumphp:
    tasks:
        composer:
            file: ./composer.json
            no_check_all: false
            no_check_lock: false
            no_check_publish: false
            no_local_repository: false
            with_dependencies: false
            strict: false
```

**file**

*Default: ./composer.json*

Specifies at which location the `composer.json` file can be found.


**no_check_all**

*Default: false*

Do not emit a warning if requirements in composer.json use unbound version constraints.


**no_check_lock**

*Default: false*

Do not emit an error if composer.lock exists and is not up to date.


**no_check_publish**

*Default: false*

Do not emit an error if composer.json is unsuitable for publishing as a package on Packagist but is otherwise valid.


**no_local_repository**

*Default: false*

Do emit an error if composer.json declares local repositories (see https://getcomposer.org/doc/05-repositories.md#path).


**with_dependencies**

*Default: false*

Also validate the `composer.json` of all installed dependencies.


**strict**

*Default: false*

Return a non-zero exit code for warnings as well as errors.
