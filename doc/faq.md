## FAQ

**Which parts of the code does GrumPHP scan?**
> When running `./vendor/bin/grumphp run` all 
> files in the repository will be scanned.
> On pre-commit + commit-msg the changed files 
> will be scanned.
> Most tasks work directly with these files, 
> but there are some commands like `git_blacklist` 
> that are able to check only the committed lines.


