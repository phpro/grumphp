# Mago Formatter

Automatically format PHP code to match the configured style preferences.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task always runs in `--dry-run` mode: it previews formatting changes without modifying any files, and fails if any file would be changed.

If the task fails, GrumPHP will offer to re-run without `--dry-run` to apply the formatting in-place.

## Config

The task lives under the `mago_format` namespace and has no configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_format: ~
```
