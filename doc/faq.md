## FAQ

### Table of content
- [How can I bypass GrumPHP](#how-can-i-bypass-grumphp)
- [Which parts of the code does GrumPHP scan?](#which-parts-of-the-code-does-grumphp-scan)
- [Does GrumPHP support automatic fixing](#does-grumphp-support-automatic-fixing)

### How can I bypass GrumPHP

You shouldn't! Its to maintain clean and well formatted code.
Don't make your co-worker pissed off again...

*Note: use `--no-verify` or `-n` flag when you commit, 
this will bypass the pre-commit and commit-msg*

[up](#table-of-content)

### Which parts of the code does GrumPHP scan

When running `./vendor/bin/grumphp run` all 
files in the repository will be scanned.
On pre-commit + commit-msg the changed files 
will be scanned.
Most tasks work directly with these files, 
but there are some commands like `git_blacklist` 
that are able to check only the committed lines.

[up](#table-of-content)

### Does GrumPHP support automatic fixing

No, GrumPHP doesn't fix things for you. He wants you to have full
control of the code you commit and not manipulate it in anyway.

[up](#table-of-content)
