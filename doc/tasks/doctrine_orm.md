# Doctrine ORM

The Doctrine ORM task will validate that your Doctrine mapping files and check if the mapping is in sync with the database.
It lives under the `doctrine_orm` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        doctrine_orm:
            skip_mapping: false
            skip_sync: false
            triggered_by: ['php', 'xml', 'yml']
```

**skip_mapping**

*Default: false*

With this parameter you can skip the mapping validation check.

**skip_sync**

*Default: false*

With this parameter you can skip checking if the mapping is in sync with the database.

**triggered_by**

*Default: [php, xml, yml]*

This is a list of extensions that should trigger the Doctrine task.
