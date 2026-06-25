# Mago Linter

Run linting rules on PHP code to identify style violations, code smells, and potential bugs.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Behavior

The task always runs in `--fix --dry-run` mode: it previews what automatic fixes would be applied without modifying any files, and fails if issues are found. When running in a `git pre-commit` context, only staged files are linted (`--staged`). In a `run` context, all files are linted.

If the task fails, GrumPHP will offer to re-run with `--fix` applied. The fix mode can be configured via `fix-mode`.

## Config

The task lives under the `mago_lint` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_lint:
            semantics: ~
            pedantic: ~
            only: []
            retain-codes: []
            ignore-baseline: ~
            sort: ~
            fix-mode: safe
            minimum-report-level: ~
```

**semantics**

*Type: bool*

Skip linter rules and only perform basic syntax and semantic validation. Checks that your PHP code parses correctly and has valid semantic structure, without applying any style or quality rules. Useful for quick syntax validation.

**pedantic**

*Type: bool*

Enable every available linter rule for maximum thoroughness. Overrides your configuration and enables all rules, including those disabled by default. The output will be extremely verbose and is not recommended for regular use. Useful for comprehensive code audits.

**only**

*Type: string[] — Default: []*

Run only the specified rules, ignoring the configuration file. Provide a list of rule codes (e.g. `invalid-argument`, `semantics`). Overrides your `mago.toml` configuration and is useful for targeted analysis.

**retain-codes**

*Type: string[] — Default: []*

Reporting filter: only display issues matching the specified rule codes (e.g. `invalid-argument`, `semantics`). All rules still run; only the output is filtered. Can be specified multiple times.

Note: this differs from `only`, which restricts which rules are executed.

**ignore-baseline**

*Type: bool*

Ignore the baseline file and report all issues, including those currently suppressed. The baseline file must be generated manually via `mago lint --generate-baseline`.

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

