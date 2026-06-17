# Mago Guard

Enforce architectural rules and layer dependencies. Checks that code follows defined architectural constraints, such as ensuring that certain layers don't depend on others.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task runs `mago guard` and fails when an architectural violation is found. It runs on all files in both `git pre-commit` and `run` contexts (guard has no `--staged` mode, and architectural rules are evaluated against the whole project). Because every pre-commit run scans the full project, consider whether `mago_guard` is fast enough for your codebase before enabling it as a pre-commit task.

Guard does not offer an auto-fix: architectural violations cannot be fixed automatically, so the task only reports them.

## Config

The task lives under the `mago_guard` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_guard:
            mode: ~
            no-stubs: ~
            retain-codes: []
            ignore-baseline: ~
            sort: ~
            minimum-report-level: ~
```

**mode**

*Default: null — Possible values: `structural`, `perimeter`*

Selects which guard checks run. These are mutually exclusive in Mago, so a single option is used instead of separate flags:

- `~` (not set) — run both structural and perimeter checks (Mago's default)
- `structural` — run only structural checks (naming conventions, modifiers, inheritance constraints)
- `perimeter` — run only perimeter checks (dependency boundaries, layer restrictions)

**no-stubs**

*Type: bool*

Disable built-in PHP and library stubs. By default, guard uses stubs for built-in PHP functions and popular libraries to provide accurate symbol information. Disabling this may result in more warnings when external symbols can't be resolved.

**retain-codes**

*Type: string[] — Default: []*

Reporting filter: only display issues matching the specified rule codes. All rules still run; only the output is filtered. Can be specified multiple times.

**ignore-baseline**

*Type: bool*

Ignore the baseline file and report all issues, including those currently suppressed. The baseline file must be generated manually via `mago guard --generate-baseline`.

**sort**

*Type: bool*

Sort reported issues by severity level, rule code, and file location. By default, issues are reported in the order they appear in files.

**minimum-report-level**

*Default: null (mago default: all levels)*

Minimum severity level to display in the report. Issues below this level are not shown. Possible values: `note`, `help`, `warning`, `error`
