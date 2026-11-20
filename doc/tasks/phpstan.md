# PHPStan

The PHPStan task focuses on finding errors in your code without actually running it. It catches whole classes of bugs even before you write tests for the code.
It lives under the `phpstan` namespace and has following configurable parameters:

## Composer
```bash
composer require --dev phpstan/phpstan
```

## Config
```yaml
# grumphp.yml
grumphp:
    tasks:
        phpstan:
            autoload_file: ~
            configuration: ~
            level: null
            force_patterns: []
            ignore_patterns: []
            triggered_by: ['php']
            memory_limit: "-1"
            use_grumphp_paths: false
```

**autoload_file**

*Default: null*

With this parameter you can specify the path your project's additional autoload file path.

**configuration**

*Default: null*

With this parameter you can specify the path your project's configuration file.

**level**

*Default: null*

With this parameter you can set the level of rule options - the higher, the stricter.
You must either specify a level in GrumPHP or inside the `phpstan.neon` file.

**force_patterns**

*Default: []*

This is a list of patterns that will be forced for analysis even when the file or path is ignored.
Without `use_grumphp_paths`, a changed file that matches one of these patterns triggers the task.

**ignore_patterns**

*Default: []*

This is a list of patterns that will be ignored by phpstan. With this option you can skip files like tests. Leave this option blank to run phpstan for every php file.
Without `use_grumphp_paths`, these files don't trigger the task, but PHPStan still analyses them if they are part of the `paths` in your PHPStan configuration.
Use `excludePaths` in your PHPStan configuration to exclude them from the analysis.

**triggered_by**

*Default: [php]*

This is a list of extensions to be sniffed.
The task is skipped when none of the changed files has one of these extensions.

**memory_limit**

*Default: null*

With this parameter you can specify the memory limit.


**use_grumphp_paths**

*Default: false*

With this parameter you can choose which files PHPStan analyses.

By default, GrumPHP runs PHPStan on the whole project, as configured by the `paths` in your PHPStan configuration.
This is what [PHPStan recommends](https://phpstan.org/blog/why-you-should-always-analyse-whole-project):
a change in one file can cause errors in other files, for example a changed method signature that breaks its callers,
and only a run on the whole project finds them. Thanks to PHPStan's result cache, such a run usually is not slower than a run on the changed files.
The task still only runs when one of the changed files triggers it, see `triggered_by`, `ignore_patterns` and `force_patterns`.
Make sure that your PHPStan configuration contains the `paths` to analyse.

When you set this parameter to `true`, GrumPHP only passes the changed files to PHPStan.
This can be faster without a result cache, but PHPStan can miss errors in other files,
and it does not report `ignoreErrors` entries that no longer match.

## Auto-fixing

Starting with PHPStan 2.3.5, PHPStan marks errors it can fix automatically (🔧) and suggests to run it again with `--fix`.
When PHPStan reports such errors, GrumPHP offers to fix them by running the same `phpstan analyse` command with the `--fix` option.
The fix is applied to the same files that were analysed.
Errors that cannot be fixed automatically are left untouched and still need to be fixed by hand.

Keep in mind that the `--fix` option is experimental in PHPStan.
Besides fixing code, it also removes unmatched `ignoreErrors` entries from your configuration file and baseline, and unmatched `@phpstan-ignore` comments.

Whether GrumPHP asks to fix the errors or fixes them by default can be configured with the [`fixer`](../parameters.md) parameters.
