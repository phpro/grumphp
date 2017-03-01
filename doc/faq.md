# FAQ

## Table of content
- [How can I bypass GrumPHP](#how-can-i-bypass-grumphp)
- [Which parts of the code does GrumPHP scan?](#which-parts-of-the-code-does-grumphp-scan)
- [Does GrumPHP support automatic fixing](#does-grumphp-support-automatic-fixing)
- [Does GrumPHP support Windows](#does-grumphp-support-windows)


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


## Does GrumPHP support automatic fixing

No, he doesn't fix things for you. He wants you to have full
control of the code you commit and not manipulate it in any way.

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
