# Mago Analyzer

Perform deep static analysis on PHP code including type checking, control flow analysis, and detection of logical errors.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task runs `mago analyze` directly to get full diagnostic output. When running in a `git pre-commit` context, only staged files are analyzed (`--staged`). In a `run` context, all files are analyzed.

If the task fails, GrumPHP will offer to re-run with `--fix` applied. The fix mode can be configured via `fix-mode`.

## Config

The task lives under the `mago_analyze` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_analyze:
            no-stubs: ~
            retain-codes: []
            ignore-baseline: ~
            sort: ~
            fix-mode: safe
            minimum-report-level: ~
```

**no-stubs**

*Type: bool*

Disable built-in PHP and library stubs for analysis. By default, the analyzer uses stubs for built-in PHP functions and popular libraries to provide accurate type information. Disabling this may result in more reported issues when external symbols can't be resolved.

**retain-codes**

*Type: string[] — Default: []*

Reporting filter: only display issues matching the specified rule codes (e.g. `invalid-argument`, `semantics`). All rules still run; only the output is filtered. Can be specified multiple times.

**ignore-baseline**

*Type: bool*

Ignore the baseline file and report all issues, including those currently suppressed. The baseline file must be generated manually via `mago analyze --generate-baseline`.

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

