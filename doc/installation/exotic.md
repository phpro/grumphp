# Installation with an exotic project structure

When your application has a project structure that is not covered by the default configuration settings,
you will have to create a `grumphp.yml` *before* installing the package
and add next config into your application's `composer.json`:

```json
# composer.json
{
    "extra": {
        "grumphp": {
            "config-default-path": "path/to/grumphp.yml"
        }
    }
}
```

You can also change the configuration after installation.
The only downfall is that you will have to initialize the git hook manually:

```sh
php ./vendor/bin/grumphp git:init --config=path/to/grumphp.yml
```
