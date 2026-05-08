# Mago Analyzer

Perform deep static analysis on PHP code including type checking, control flow analysis, and detection of logical errors.

## Composer

```bash
composer require --dev carthage-software/mago
```

## Config

The task lives under the `mago_analyze` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        mago_analyze:
            no-stubs: ~
            staged: ~
            retain-codes: []
            ignore-baseline: ~
            fix: ~
            fail-on-remaining: ~
            sort: ~
            fixable-only: ~
            reporting-format: ~
            reporting-target: ~
            minimum-report-level: ~
            minimum-fail-level: ~
            dry-run: ~
```

**no-stubs**

*Type: bool*

Disable built-in PHP and library stubs for analysis. By default, the analyzer uses stubs for built-in PHP functions and popular libraries to provide accurate type information. Disabling this may result in more reported issues when external symbols can't be resolved.

**staged**

*Type: bool*

Only analyze files that are staged in git. Designed for git pre-commit hooks. Fails if not in a git repository.

**retain-codes**

*Type: string[] — Default: []*

Reporting filter: only display issues matching the specified rule codes (e.g. `invalid-argument`, `semantics`). All rules still run; only the output is filtered. Can be specified multiple times.

**ignore-baseline**

*Type: bool*

Ignore the baseline file and report all issues, including those currently suppressed. The baseline file must be generated manually via `mago analyze --generate-baseline`.

**fix**

*Default: null*

Apply automatic fixes to the source code. Accepted values:

- `safe` — apply only safe fixes (default fix mode)
- `potentially-unsafe` — also apply fixes that may require manual review
- `unsafe` — also apply fixes that might change code behavior

Cannot be used together with `fixable-only`, `reporting-format`, or `reporting-target`.

**fail-on-remaining**

*Type: bool*

Exit with a non-zero status if there are issues remaining after fixing. Useful in CI/CD pipelines to ensure all issues are addressed. Requires `fix` to be set.

**sort**

*Type: bool*

Sort reported issues by severity level, rule code, and file location. By default, issues are reported in the order they appear in files.

**fixable-only**

*Type: bool*

Filter output to only show issues that can be automatically fixed. Cannot be used together with `fix`.

**reporting-format**

*Default: null (mago default: medium)*

Output format for issue reports. Not available when using `fix`. Possible values:

`rich`, `medium`, `short`, `ariadne`, `github`, `gitlab`, `json`, `count`, `code-count`, `checkstyle`, `emacs`, `sarif`

**reporting-target**

*Default: null (mago default: stdout)*

Where to send the output. Not available when using `fix`. Possible values: `stdout`, `stderr`

**minimum-report-level**

*Default: null (mago default: all levels)*

Minimum severity level to display in the report. Issues below this level are not shown. Possible values: `note`, `help`, `warning`, `error`

**minimum-fail-level**

*Default: null (mago default: error)*

Minimum severity level that causes the command to fail. For example, setting this to `warning` means the command fails on warnings and errors, but not on notes or help suggestions. Possible values: `note`, `help`, `warning`, `error`

**dry-run**

*Type: bool*

Preview fixes without writing any changes to disk. Shows what changes would be made without modifying any files. Requires `fix` to be set.
