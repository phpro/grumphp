# Mago Formatter

Automatically format PHP code to match the configured style preferences.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task always runs in `--dry-run` mode: it previews formatting changes without modifying any files, and fails if any file would be changed.

In a `run` context the whole project is checked (Mago uses the paths from your `mago.toml`). In a `git pre-commit` context the staged `.php` files are passed to Mago explicitly so only those files are checked. (`mago format --staged` cannot be combined with `--dry-run`, so the staged files are passed as paths instead — note this overrides the `source`/`excludes` config in `mago.toml` for those files, the same trade-off other file-based GrumPHP tasks make.)

If the task fails, GrumPHP will offer to re-run without `--dry-run` to apply the formatting in-place.

## Config

The task lives under the `mago_format` namespace and has no configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_format: ~
```
