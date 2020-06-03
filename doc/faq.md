# FAQ

## Table of content
- [How can I bypass GrumPHP](#how-can-i-bypass-grumphp)
- [Which parts of the code does GrumPHP scan?](#which-parts-of-the-code-does-grumphp-scan)
- [Does GrumPHP support Windows](#does-grumphp-support-windows)
- [How can I fix Composer require conflicts?](#how-can-i-fix-composer-require-conflicts)
- [Why is the unstaged file state being used?](#why-is-the-unstaged-file-state-being-used)
- [How can I fix the SourceTree $PATH problem?](#how-can-i-fix-the-sourcetree-path-problem)


## How can I bypass GrumPHP

You shouldn't! Its to maintain clean and well formatted code.
Don't make your co-worker pissed off again...

*Note: use `--no-verify` or `-n` flag when you commit, 
this will bypass the pre-commit and commit-msg*

[up](#table-of-content)


## Which parts of the code does GrumPHP scan

When running `./vendor/bin/grumphp run` all 
files in the repository will be scanned.
On pre-commit + commit-msg the changed files 
will be scanned.
Most tasks work directly with these files, 
but there are some commands like `git_blacklist` 
that are able to check only the committed lines.

[up](#table-of-content)


## Does GrumPHP support Windows

Yes, he does. But there are some limitations.

**PHPCS and PHPLint tasks fail on Windows 7**

This is due to the cmd input limit on windows.
The problem is that the CLI input string on cmd.exe 
is limited to 8191 characters. Tasks like phplint 
and phpcs contain the paths to the files that are 
being checked. During a run command, the list of 
files wil exceed this amount which results in some 
strange errors on windows.

[up](#table-of-content)


## How can I fix Composer require conflicts?

In some cases, you might get require conflicts between your project and GrumPHP for Composer packages.

For example, Magento 2 has the following requirement for `symfony/console`

    "symfony/console": "~2.3, !=2.7.0"
    
On the other hand, grumPHP has this requirement

    "symfony/console": "~2.7|~3.0"

If you run composer, you will get the following error message

    Your requirements could not be resolved to an installable set of packages.

You can resolve this problem by adding the following (or similar) to the composer.json file of your project

    "symfony/console": "v2.8.20 as v2.6.13"

[up](#table-of-content)


## Why is the unstaged file state being used?

GrumPHP can only work with the actual files on the filesystem. This means that your unstaged changes will be staged when GrumPHP checks your codebase. It is possible to use the staged files by stashing your changes with the `ignore_unstaged_changes` parameter. Do note that this parameter is risky and won't work with partial commits. [More information can be found here](https://github.com/phpro/grumphp/blob/master/doc/parameters.md).

[up](#table-of-content)

## How can I fix the SourceTree $PATH problem?

For example:
- Warning: Unsupported declare 'strict_types' in vendor/ocramius/proxy-manager/src/ProxyManager/Configuration.php on line 19
- Parse error: parse error, expecting ';'' or'{'' in vendor/ocramius/proxy-manager/src/ProxyManager/Configuration.php on line 87

SourceTree probably doesn't import your local $PATH variable before running the scripts. This causes a lot of issues like a different PHP version than the one installed locally or exectuables that can't be found.

You can fix this by adding following line to the top of the git hooks:

```bash
export PATH=/usr/local/bin:$PATH
```

[up](#table-of-content)
