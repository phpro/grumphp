# Global installation

It is possible to install or update GrumPHP on your system with following commands:

```sh
composer global require phpro/grumphp
composer global update phpro/grumphp
```

This will install the `grumphp` executable in the `~/.composer/vendor/bin` folder.
Make sure to add this folder to your system `$PATH` variable:

```sh
# .zshrc or .bashrc
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

That's all! The `grumphp` command will be available on your CLI and will be used by default.

**Note:** that you might want to re-initialize your project git hooks to make sure the system-wide executable is being used. Run the `grumphp git:init` command in the project directory.

**Note:** When you globally installed 3rd party tools like e.g. `phpunit`, those will also be used instead of the composer executables.
