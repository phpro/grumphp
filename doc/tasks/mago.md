# Mago

[Mago](https://mago.carthage.software/) is a fast PHP toolchain written in Rust. It bundles a
formatter, a linter, a static analyzer and an architectural guard. GrumPHP exposes each of these as
its own task so you can enable only what you need and configure them independently.

## Composer

```bash
composer require --dev carthage-software/mago
```

Mago is configured through a single `mago.toml` file in your project root. You can scaffold one with:

```bash
vendor/bin/mago init
```

## Tasks

| Task | Description |
| --- | --- |
| [`mago_format`](mago/formatter.md) | Format PHP code to match your configured style. |
| [`mago_lint`](mago/linter.md) | Run linting rules to catch style violations, code smells and likely bugs. |
| [`mago_analyze`](mago/analyzer.md) | Deep static analysis: type checking, control-flow and logical-error detection. |
| [`mago_guard`](mago/guard.md) | Enforce architectural rules and layer dependencies. |

## Behavior

`mago_format`, `mago_lint` and `mago_analyze` run read-only by default and, when they fail, GrumPHP
offers to re-run them with fixes applied. `mago_guard` has no auto-fix — architectural violations
cannot be fixed automatically, so it only reports them.

Each task scopes its work to the relevant files per context (pre-commit vs run). The exact behavior
differs per task because of how Mago's CLI works — see each task's page below for the details and its
full set of configurable options.
