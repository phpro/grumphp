# Mago Formatter

Automatically format PHP code to match the configured style preferences.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Config

The task lives under the `mago_format` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_format:
            type: default
```

**type**

*Default: default — Possible values: `default`, `dry-run`, `check`, `staged`*

Controls how the formatter runs:

- `default` — apply formatting changes in-place
- `dry-run` — print a diff of changes without modifying any files
- `check` — exit with failure if any file would be changed, without modifying files. Ideal for CI environments
- `staged` — format files currently staged in git. Designed for git pre-commit hooks. Fails if not in a git repository
