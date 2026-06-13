# Mago Guard

Enforce architectural rules and layer dependencies. Checks that code follows defined architectural constraints, such as ensuring that certain layers don't depend on others.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task always runs in `--fix --dry-run` mode: it previews what automatic fixes would be applied without modifying any files, and fails if issues are found. The task runs on all files in both `git pre-commit` and `run` contexts.

If the task fails, GrumPHP will offer to re-run with `--fix` applied. The fix mode can be configured via `fix-mode`.

## Config

The task lives under the `mago_guard` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_guard:
            no-stubs: ~
            structural: ~
            perimeter: ~
            retain-codes: []
            ignore-baseline: ~
            sort: ~
            fix-mode: safe
            minimum-report-level: ~
```

**no-stubs**

*Type: bool*

Disable built-in PHP and library stubs. By default, guard uses stubs for built-in PHP functions and popular libraries to provide accurate symbol information. Disabling this may result in more warnings when external symbols can't be resolved.

**structural**

*Type: bool*

Run only structural checks (naming conventions, modifiers, inheritance constraints). Can be combined with `perimeter`. When neither is set, both check types run.

**perimeter**

*Type: bool*

Run only perimeter checks (dependency boundaries, layer restrictions). Can be combined with `structural`. When neither is set, both check types run.

**retain-codes**

*Type: string[] — Default: []*

Reporting filter: only display issues matching the specified rule codes. All rules still run; only the output is filtered. Can be specified multiple times.

**ignore-baseline**

*Type: bool*

Ignore the baseline file and report all issues, including those currently suppressed. The baseline file must be generated manually via `mago guard --generate-baseline`.

**sort**

*Type: bool*

Sort reported issues by severity level, rule code, and file location. By default, issues are reported in the order they appear in files.

**fix-mode**

*Default: safe — Possible values: `safe`, `potentially-unsafe`, `unsafe`*

Controls which fixes are applied when GrumPHP offers to auto-fix:

- `safe` — apply only safe fixes (default)
- `potentially-unsafe` — also apply fixes that may require manual review
- `unsafe` — also apply fixes that might change code behavior

**minimum-report-level**

*Default: null (mago default: all levels)*

Minimum severity level to display in the report. Issues below this level are not shown. Possible values: `note`, `help`, `warning`, `error`

