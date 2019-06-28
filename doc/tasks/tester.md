# Tester

The Netter Tester task will run your unit tests.

***Composer***

```
composer require nette/tester --dev
```

***Config***

The task lives under the `tester` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        tester:
            always_execute: false
```

**always_execute**

*Default: false*

Always run the whole test suite, even if no PHP files were changed.

